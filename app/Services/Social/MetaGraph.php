<?php

namespace App\Services\Social;

use App\Models\SocialAccount;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Official Meta Graph API only (Facebook Login for Business + Pages API + Instagram
 * Graph API). Tokens go in the Authorization header or POST body, never in a logged URL.
 */
class MetaGraph
{
    private function base(): string
    {
        return 'https://graph.facebook.com/'.config('services.meta.graph_version');
    }

    public function isConfigured(): bool
    {
        return (bool) (config('services.meta.app_id') && config('services.meta.app_secret') && config('services.meta.config_id'));
    }

    public function redirectUri(): string
    {
        return route('admin.social.meta.callback');
    }

    public function loginUrl(string $state): string
    {
        return 'https://www.facebook.com/'.config('services.meta.graph_version').'/dialog/oauth?'.http_build_query([
            'client_id' => config('services.meta.app_id'),
            'redirect_uri' => $this->redirectUri(),
            'config_id' => config('services.meta.config_id'),
            'response_type' => 'code',
            'override_default_response_type' => 'true',
            'state' => $state,
        ]);
    }

    /** code → short-lived user token → long-lived user token (~60 days). */
    public function userTokenFromCode(string $code): string
    {
        $short = $this->check(Http::asForm()->timeout(20)->post($this->base().'/oauth/access_token', [
            'client_id' => config('services.meta.app_id'),
            'client_secret' => config('services.meta.app_secret'),
            'redirect_uri' => $this->redirectUri(),
            'code' => $code,
        ]))->json('access_token');

        return $this->check(Http::asForm()->timeout(20)->post($this->base().'/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.meta.app_id'),
            'client_secret' => config('services.meta.app_secret'),
            'fb_exchange_token' => $short,
        ]))->json('access_token');
    }

    /**
     * Pages the person manages. A Page token taken from a long-lived user token does not expire.
     *
     * @return array<int, array{id:string,name:string,access_token:string,instagram?:array{id:string,username:?string,name:?string}}>
     */
    public function pages(string $userToken): array
    {
        $data = $this->check(Http::withToken($userToken)->timeout(20)->get($this->base().'/me/accounts', [
            'fields' => 'id,name,access_token,instagram_business_account{id,username,name}',
            'limit' => 50,
        ]))->json('data', []);

        return array_map(fn ($p) => array_filter([
            'id' => (string) $p['id'],
            'name' => $p['name'] ?? 'Page',
            'access_token' => $p['access_token'] ?? '',
            'instagram' => isset($p['instagram_business_account']['id']) ? [
                'id' => (string) $p['instagram_business_account']['id'],
                'username' => $p['instagram_business_account']['username'] ?? null,
                'name' => $p['instagram_business_account']['name'] ?? null,
            ] : null,
        ]), $data);
    }

    /** @return array{id:string, permalink:?string} */
    public function publishFacebook(SocialAccount $page, string $caption, ?string $link, ?string $imageUrl): array
    {
        $token = $this->tokenOf($page);
        if ($imageUrl) {
            // Photo post; the link goes in the text so it stays clickable.
            $res = $this->check(Http::asForm()->timeout(60)->post($this->base()."/{$page->external_id}/photos", [
                'url' => $imageUrl,
                'caption' => trim($caption.($link ? "\n\n".$link : '')),
                'access_token' => $token,
            ]));
            $id = (string) ($res->json('post_id') ?? $res->json('id'));
        } else {
            $res = $this->check(Http::asForm()->timeout(60)->post($this->base()."/{$page->external_id}/feed", array_filter([
                'message' => $caption,
                'link' => $link,
                'access_token' => $token,
            ])));
            $id = (string) $res->json('id');
        }

        $permalink = Http::withToken($token)->timeout(20)->get($this->base()."/{$id}", ['fields' => 'permalink_url'])->json('permalink_url');

        return ['id' => $id, 'permalink' => $permalink];
    }

    /** Instagram needs a public image URL. Two steps: create container, then publish it. */
    public function publishInstagram(SocialAccount $ig, string $caption, string $imageUrl): array
    {
        $token = $this->tokenOf($ig);
        $container = (string) $this->check(Http::asForm()->timeout(60)->post($this->base()."/{$ig->external_id}/media", [
            'image_url' => $imageUrl,
            'caption' => $caption,
            'access_token' => $token,
        ]))->json('id');

        // Image containers are usually ready at once; give Meta a few seconds if not.
        for ($i = 0; $i < 5; $i++) {
            $status = Http::withToken($token)->timeout(20)->get($this->base()."/{$container}", ['fields' => 'status_code'])->json('status_code');
            if ($status === null || $status === 'FINISHED') break;
            if ($status === 'ERROR') throw new SocialPublishException('Instagram could not process the image (check it is a public JPG/PNG, 320–1440 px wide, aspect 4:5 to 1.91:1).');
            usleep(app()->runningUnitTests() ? 0 : 2_000_000);
        }

        $id = (string) $this->check(Http::asForm()->timeout(60)->post($this->base()."/{$ig->external_id}/media_publish", [
            'creation_id' => $container,
            'access_token' => $token,
        ]))->json('id');

        $permalink = Http::withToken($token)->timeout(20)->get($this->base()."/{$id}", ['fields' => 'permalink'])->json('permalink');

        return ['id' => $id, 'permalink' => $permalink];
    }

    private function tokenOf(SocialAccount $account): string
    {
        return $account->token() ?? throw tap(new SocialPublishException('Saved token cannot be read (APP_KEY changed?). Reconnect Facebook.'), fn ($e) => $e->permanent = true);
    }

    private function check(Response $res): Response
    {
        if ($res->successful() && ! $res->json('error')) {
            return $res;
        }

        $err = $res->json('error') ?? [];
        if (is_array($err)) {
            // Meta often sends error_user_msg as "", so skip empty parts instead of using ??.
            $parts = array_filter([
                trim((string) ($err['error_user_title'] ?? '')),
                trim((string) ($err['error_user_msg'] ?? '')),
                trim((string) ($err['message'] ?? '')),
            ], fn ($v) => $v !== '');
            $codes = array_filter(['code '.($err['code'] ?? ''), 'subcode '.($err['error_subcode'] ?? '')], fn ($v) => ! str_ends_with($v, ' '));
            $msg = implode(' — ', array_unique($parts)) ?: 'Meta API error (HTTP '.$res->status().')';
            if ($codes) $msg .= ' ['.implode(', ', $codes).']';
        } else {
            $msg = trim((string) $err) ?: 'Meta API error (HTTP '.$res->status().'): '.mb_substr(trim($res->body()), 0, 200);
        }
        $e = new SocialPublishException(mb_substr("Meta: {$msg}", 0, 480));
        // 190 = token invalid/expired, 200/10 = permission missing: retrying won't help.
        $e->permanent = in_array((int) ($err['code'] ?? 0), [10, 100, 190, 200], true) || $res->status() === 400;

        throw $e;
    }
}
