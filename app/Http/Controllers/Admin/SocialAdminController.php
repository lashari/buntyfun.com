<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\Social\MetaGraph;
use App\Services\Social\PostSuggestions;
use App\Services\Social\SocialPublishException;
use App\Services\Social\SocialPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Social manager: connect the Facebook Page + Instagram (official login), compose posts,
 * approve, schedule. Editors can draft; admins approve; only a super admin connects accounts.
 */
class SocialAdminController extends Controller
{
    public function index(Request $request, MetaGraph $graph, PostSuggestions $suggestions)
    {
        $status = $request->query('status', 'open');
        $query = SocialPost::with('account')->latest('updated_at');
        if ($status === 'open') {
            $query->whereIn('status', ['draft', 'approved', 'publishing', 'failed']);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.social.index', [
            'accounts' => SocialAccount::orderBy('platform')->get(),
            'posts' => $query->paginate(25)->withQueryString(),
            'status' => $status,
            'configured' => $graph->isConfigured(),
            'top' => $suggestions->top(8),
            'stats' => [
                'published_30d' => SocialPost::where('status', 'published')->where('published_at', '>=', now()->subDays(30))->count(),
                'scheduled' => SocialPost::where('status', 'approved')->count(),
                'drafts' => SocialPost::where('status', 'draft')->count(),
                'failed' => SocialPost::where('status', 'failed')->count(),
            ],
        ]);
    }

    public function connect(Request $request, MetaGraph $graph)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        if (! $graph->isConfigured()) {
            return back()->withErrors('Add META_APP_SECRET to the server .env first.');
        }
        $state = Str::random(40);
        $request->session()->put('meta_oauth_state', $state);

        return redirect()->away($graph->loginUrl($state));
    }

    public function callback(Request $request, MetaGraph $graph)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        $expected = $request->session()->pull('meta_oauth_state');
        if (! $expected || ! hash_equals($expected, (string) $request->query('state'))) {
            return redirect()->route('admin.social.index')->withErrors('Facebook login expired or was not started here. Click Connect again.');
        }
        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect()->route('admin.social.index')->withErrors('Facebook connection was cancelled.');
        }

        try {
            $userToken = $graph->userTokenFromCode((string) $request->query('code'));
            $pages = $graph->pages($userToken);
        } catch (SocialPublishException $e) {
            return redirect()->route('admin.social.index')->withErrors($e->getMessage());
        }
        if (! $pages) {
            return redirect()->route('admin.social.index')->withErrors('No Facebook Page was shared. Connect again and tick the BuntyFun Page (and its Instagram).');
        }

        $saved = [];
        foreach ($pages as $p) {
            $fb = SocialAccount::updateOrCreate(['platform' => 'facebook', 'external_id' => $p['id']], [
                'name' => $p['name'], 'access_token' => $p['access_token'], 'token_expires_at' => null,
                'enabled' => true, 'last_error' => null, 'connected_by' => $request->user()->id,
            ]);
            $saved[] = $fb->label();
            AuditLog::record($request->user(), 'social_connected', 'social_account', $fb->id);
            if (! empty($p['instagram'])) {
                $ig = SocialAccount::updateOrCreate(['platform' => 'instagram', 'external_id' => $p['instagram']['id']], [
                    'name' => $p['instagram']['name'] ?? $p['name'], 'username' => $p['instagram']['username'],
                    'access_token' => $p['access_token'], 'page_id' => $p['id'], 'token_expires_at' => null,
                    'enabled' => true, 'last_error' => null, 'connected_by' => $request->user()->id,
                ]);
                $saved[] = $ig->label();
                AuditLog::record($request->user(), 'social_connected', 'social_account', $ig->id);
            }
        }

        return redirect()->route('admin.social.index')->with('status', 'Connected: '.implode(', ', $saved).'.');
    }

    public function disconnect(Request $request, SocialAccount $account)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        AuditLog::record($request->user(), 'social_disconnected', 'social_account', $account->id);
        SocialPost::where('social_account_id', $account->id)->whereIn('status', ['draft', 'approved'])->update(['status' => 'cancelled']);
        $account->delete();

        return back()->with('status', 'Disconnected '.$account->label().'. Its waiting posts were cancelled.');
    }

    public function create(Request $request, PostSuggestions $suggestions)
    {
        $prefill = null;
        if ($request->filled('type') && $request->filled('id')) {
            $prefill = $suggestions->fromSource((string) $request->query('type'), (int) $request->query('id'));
        }

        return view('admin.social.create', [
            'accounts' => SocialAccount::where('enabled', true)->orderBy('platform')->get(),
            'prefill' => $prefill,
            'sourceType' => $prefill ? $request->query('type') : null,
            'sourceId' => $prefill ? (int) $request->query('id') : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'accounts' => ['required', 'array', 'min:1'],
            'accounts.*' => ['integer', 'exists:social_accounts,id'],
            'caption' => ['required', 'string', 'max:2000'],
            'link' => ['nullable', 'url', 'max:400'],
            'image_url' => ['nullable', 'url', 'max:400'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:8192', 'dimensions:min_width=320'],
            'scheduled_at' => ['nullable', 'date'],
            'action' => ['required', 'in:draft,approve'],
            'source_type' => ['nullable', 'string', 'max:40'],
            'source_id' => ['nullable', 'integer'],
        ]);
        if ($data['action'] === 'approve') {
            abort_unless($request->user()->canPublish(), 403);
        }

        $image = $data['image_url'] ?? null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('social', 'public');
            $image = Storage::disk('public')->url($path);
        }

        $accounts = SocialAccount::whereIn('id', $data['accounts'])->where('enabled', true)->get();
        if ($accounts->contains('platform', 'instagram') && ! $image) {
            return back()->withInput()->withErrors('Instagram needs an image: upload one or give an image URL.');
        }

        $when = ! empty($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null;
        foreach ($accounts as $acc) {
            $post = SocialPost::create([
                'platform' => $acc->platform, 'social_account_id' => $acc->id,
                'source_type' => $data['source_type'] ?? null, 'source_id' => $data['source_id'] ?? null,
                'caption' => $data['caption'], 'image_url' => $image, 'scheduled_at' => $when,
                'status' => $data['action'] === 'approve' ? 'approved' : 'draft',
                'created_by' => $request->user()->id,
                'approved_by' => $data['action'] === 'approve' ? $request->user()->id : null,
            ]);
            // UTM-tagged link per post, so GA4 shows which post sent each visit.
            if (! empty($data['link'])) {
                $tracked = SocialPost::trackedLink($data['link'], $acc->platform, $post->id);
                $post->update(['link' => $tracked]); // Instagram can't show it as a clickable link; kept for reference
            }
            AuditLog::record($request->user(), 'social_post_'.$post->status, 'social_post', $post->id);
        }

        return redirect()->route('admin.social.index')->with('status', $accounts->count().' post(s) '.($data['action'] === 'approve' ? 'approved'.($when ? ' for '.$when->format('m-d-Y H:i') : ' — going out within 5 minutes') : 'saved as drafts for approval').'.');
    }

    public function approve(Request $request, SocialPost $post)
    {
        abort_unless($request->user()->canPublish(), 403);
        $changed = SocialPost::whereKey($post->id)->where('status', 'draft')->update(['status' => 'approved', 'approved_by' => $request->user()->id, 'updated_at' => now()]);
        AuditLog::record($request->user(), 'social_post_approved', 'social_post', $post->id);

        return back()->with('status', $changed ? 'Approved.' : 'Post was not a draft.');
    }

    public function publishNow(Request $request, SocialPost $post, SocialPublisher $publisher)
    {
        abort_unless($request->user()->canPublish(), 403);
        SocialPost::whereKey($post->id)->whereIn('status', ['draft', 'approved'])
            ->update(['status' => 'approved', 'approved_by' => $request->user()->id, 'scheduled_at' => now(), 'updated_at' => now()]);
        $result = $publisher->publish($post->id);

        return back()->with('status', match ($result?->status) {
            'published' => 'Posted ✓ '.($result->permalink ?? ''),
            null => 'Already posting or posted.',
            default => 'Not posted: '.$result->error,
        });
    }

    public function cancel(Request $request, SocialPost $post)
    {
        abort_unless($request->user()->canPublish() || $post->created_by === $request->user()->id, 403);
        SocialPost::whereKey($post->id)->whereIn('status', ['draft', 'approved', 'failed'])->update(['status' => 'cancelled', 'updated_at' => now()]);

        return back()->with('status', 'Cancelled.');
    }

    public function retry(Request $request, SocialPost $post)
    {
        abort_unless($request->user()->canPublish(), 403);
        SocialPost::whereKey($post->id)->where('status', 'failed')->update(['status' => 'approved', 'attempts' => 0, 'scheduled_at' => now(), 'updated_at' => now()]);

        return back()->with('status', 'Queued again; goes out within 5 minutes.');
    }
}
