<?php

namespace App\Services\Social;

use App\Models\AuditLog;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Log;
use Throwable;

class SocialPublisher
{
    public function __construct(private readonly MetaGraph $graph) {}

    /** Publishes approved posts that are due. Returns [published, failed, errors]. */
    public function publishDue(int $limit = 5): array
    {
        $done = $failed = 0;
        $errors = [];
        $due = SocialPost::where('status', 'approved')
            ->where(fn ($q) => $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now()))
            ->orderBy('scheduled_at')->limit($limit)->pluck('id');

        foreach ($due as $id) {
            $post = $this->publish($id);
            if ($post?->status === 'published') {
                $done++;
            } elseif ($post) {
                $failed++;
                $errors[] = "#{$post->id} {$post->platform}: {$post->error}";
            }
        }

        return [$done, $failed, $errors];
    }

    /** Claims the post (approved → publishing) so two runs can never post it twice. */
    public function publish(int $id): ?SocialPost
    {
        $claimed = SocialPost::whereKey($id)->where('status', 'approved')->update(['status' => 'publishing', 'updated_at' => now()]);
        if (! $claimed) {
            return null;
        }

        $post = SocialPost::with('account')->find($id);
        try {
            $account = $post->account;
            if (! $account || ! $account->enabled) {
                throw tap(new SocialPublishException('The account is disconnected or switched off.'), fn ($e) => $e->permanent = true);
            }
            if ($post->platform === 'instagram' && ! $post->image_url) {
                throw tap(new SocialPublishException('Instagram posts need an image.'), fn ($e) => $e->permanent = true);
            }

            $result = $post->platform === 'instagram'
                ? $this->graph->publishInstagram($account, $post->caption, $post->image_url)
                : $this->graph->publishFacebook($account, $post->caption, $post->link, $post->image_url);

            $post->update(['status' => 'published', 'published_at' => now(), 'external_id' => $result['id'],
                'permalink' => $result['permalink'], 'error' => null, 'attempts' => $post->attempts + 1]);
            AuditLog::recordSystem('social_published', 'social_post', $post->id);
        } catch (Throwable $e) {
            $permanent = $e instanceof SocialPublishException && $e->permanent;
            $attempts = $post->attempts + 1;
            if (! $e instanceof SocialPublishException) {
                Log::error('Social publish crashed', ['post' => $post->id, 'error' => $e->getMessage()]);
            }
            $message = $e instanceof SocialPublishException ? $e->getMessage() : 'Unexpected error (see laravel.log).';
            // Temporary problems: back to approved, try again in 15 min (max 3 tries).
            $retry = ! $permanent && $attempts < 3;
            $post->update([
                'status' => $retry ? 'approved' : 'failed',
                'scheduled_at' => $retry ? now()->addMinutes(15) : $post->scheduled_at,
                'error' => mb_substr($message, 0, 500),
                'attempts' => $attempts,
            ]);
            if ($post->account && $permanent) {
                $post->account->update(['last_error' => mb_substr($message, 0, 300)]);
            }
        }

        return $post->fresh();
    }
}
