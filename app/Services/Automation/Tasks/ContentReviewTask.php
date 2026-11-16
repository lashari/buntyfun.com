<?php

namespace App\Services\Automation\Tasks;

use App\Models\AutomationRun;
use App\Models\ContentView;
use App\Models\Message;
use App\Services\Automation\AutomationTask;
use Illuminate\Support\Facades\DB;

/**
 * Report only: nothing is unpublished automatically.
 */
class ContentReviewTask implements AutomationTask
{
    private const TYPES = ['birthday_messages' => 'birthday_message', 'messages' => 'message', 'urdu_contents' => 'urdu_content', 'name_entries' => 'name_entry'];

    public function handle(AutomationRun $run): ?string
    {
        $seasonal = Message::where('status', 'published')->whereIn('category', Message::SEASONAL)
            ->where('updated_at', '<', now()->subMonths(11))->get(['id', 'category', 'title', 'slug'])
            ->map(fn ($m) => ['id' => $m->id, 'category' => $m->category, 'title' => $m->title, 'url' => route('messages.show', $m)])->all();

        $since = now()->subDays(90);
        $noViews = [];
        foreach (self::TYPES as $table => $type) {
            $viewed = ContentView::where('content_type', $type)->where('created_at', '>=', $since)->distinct()->pluck('content_id');
            $count = DB::table($table)->where('status', 'published')->where('created_at', '<', $since)->whereNotIn('id', $viewed)->count();
            if ($count) {
                $noViews[$type] = $count;
            }
        }

        $run->items = count($seasonal) + array_sum($noViews);
        $run->details = ['seasonal_to_refresh' => $seasonal, 'no_views_90_days' => $noViews];

        return count($seasonal).' seasonal item(s) to refresh; '.array_sum($noViews).' published item(s) older than 90 days with no views.';
    }
}
