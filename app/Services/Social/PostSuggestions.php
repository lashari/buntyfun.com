<?php

namespace App\Services\Social;

use App\Models\AiDraft;
use App\Models\BirthdayMessage;
use App\Models\Message;
use App\Models\NameEntry;
use App\Models\Quiz;
use App\Models\UrduContent;
use App\Models\Wallpaper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pre-fills a post from a content item, and ranks content worth (re)posting by real
 * views + shares in the last 30 days.
 */
class PostSuggestions
{
    /** @return array{caption:string, link:string, image_url:?string, title:string}|null */
    public function fromSource(string $type, int $id): ?array
    {
        $class = Relation::getMorphedModel($type);
        $item = $class ? $class::find($id) : null;
        if (! $item || $item->status !== 'published') {
            return null;
        }

        $draftCaption = AiDraft::where('target_type', $type)->where('target_id', $id)->first()?->payload['social_caption'] ?? null;
        [$title, $text, $link, $image] = $this->describe($item);
        $caption = $draftCaption ?: trim(Str::limit($text, 400)."\n\n#BuntyFun");

        return ['caption' => $caption, 'link' => $link, 'image_url' => $image, 'title' => $title];
    }

    /** @return array<int, array{type:string,id:int,title:string,views:int,shares:int,posted:int}> */
    public function top(int $limit = 10): array
    {
        $since = now()->subDays(30);
        $views = DB::table('content_views')->where('created_at', '>=', $since)
            ->selectRaw('content_type, content_id, count(*) c')->groupBy('content_type', 'content_id')->get();
        $shares = DB::table('share_events')->where('created_at', '>=', $since)
            ->selectRaw('content_type, content_id, count(*) c')->groupBy('content_type', 'content_id')->get()
            ->keyBy(fn ($r) => $r->content_type.':'.$r->content_id);

        $rows = [];
        foreach ($views as $v) {
            $key = $v->content_type.':'.$v->content_id;
            $rows[$key] = ['type' => $v->content_type, 'id' => (int) $v->content_id, 'views' => (int) $v->c, 'shares' => (int) ($shares[$key]->c ?? 0)];
        }
        foreach ($shares as $key => $s) {
            $rows[$key] ??= ['type' => $s->content_type, 'id' => (int) $s->content_id, 'views' => 0, 'shares' => (int) $s->c];
        }

        return collect($rows)->sortByDesc(fn ($r) => $r['views'] + 5 * $r['shares'])->take($limit * 2)
            ->map(function ($r) {
                $s = $this->fromSource($r['type'], $r['id']);
                if (! $s) return null;
                $r['title'] = $s['title'];
                $r['posted'] = DB::table('social_posts')->where('source_type', $r['type'])->where('source_id', $r['id'])->where('status', 'published')->count();

                return $r;
            })->filter()->take($limit)->values()->all();
    }

    private function describe(Model $item): array
    {
        return match (true) {
            $item instanceof BirthdayMessage => [Str::headline($item->tone).' birthday wish for '.Str::lower(Str::headline($item->relationship)), $item->body, route('birthday-messages.show', $item), null],
            $item instanceof Message => [$item->title ?: $item->categoryLabel(), $item->body, route('messages.show', $item), null],
            $item instanceof UrduContent => ['Urdu: '.$item->category, $item->body, route('urdu-content.show', $item), null],
            $item instanceof NameEntry => [$item->name_en.' — name meaning', $item->name_en.': '.$item->meaning, route('name-entries.show', $item), null],
            $item instanceof Quiz => [$item->title, 'Try this quiz: '.$item->title, route('quizzes.show', $item), null],
            $item instanceof Wallpaper => [$item->title, $item->title.' — free wallpaper download', route('wallpapers.show', $item), $item->url()],
            default => [class_basename($item).' #'.$item->getKey(), '', url('/'), null],
        };
    }
}
