<?php

namespace App\Http\Controllers;

use App\Models\BirthdayMessage;
use App\Models\Dua;
use App\Models\Message;
use App\Models\Naat;
use App\Models\Poem;
use App\Models\Quote;
use App\Models\UrduContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Listen to Text: one page where visitors paste, type or upload text (read in the browser only)
 * or open published BuntyFun content (?from=type:slug) and hear it with their device's voice.
 * The Quran is never read by a synthetic voice: Quranic duas give only their translation here.
 */
class ListenController extends Controller
{
    public const TYPES = ['dua', 'birthday', 'message', 'urdu', 'poem', 'quote', 'naat'];

    public function index(Request $request)
    {
        $from = null;
        if (is_string($request->query('from')) && preg_match('/^([a-z]+):([a-z0-9-]{1,190})$/', $request->query('from'), $m)) {
            $from = $this->content($m[1], $m[2]);
        }

        return view('listen.index', [
            'from' => $from,
            'picks' => $this->picks(),
            // ?from= pages are the same tool with different text: one canonical URL, not indexed
            'noindex' => $request->query->count() > 0,
        ]);
    }

    /** Published content as ['title', 'text', 'url', 'notice'], or null. */
    public function content(string $type, string $key): ?array
    {
        $pub = fn ($q) => $q->where('status', 'published');

        return match ($type) {
            'dua' => ($d = $pub(Dua::query())->where('slug', $key)->first()) ? $this->dua($d) : null,
            'birthday' => ($b = $pub(BirthdayMessage::query())->where('slug', $key)->first())
                ? ['title' => 'Birthday wish', 'text' => $b->body, 'url' => route('birthday-messages.show', $b->slug), 'notice' => ''] : null,
            'message' => ($x = $pub(Message::query())->where('slug', $key)->first())
                ? ['title' => $x->title ?: 'Message', 'text' => $x->body, 'url' => route('messages.show', $x->slug), 'notice' => ''] : null,
            'urdu' => ($u = $pub(UrduContent::query())->where('slug', $key)->first())
                ? ['title' => 'Urdu corner', 'text' => $u->body, 'url' => route('urdu-content.show', $u->slug), 'notice' => ''] : null,
            'poem' => ($p = $pub(Poem::query())->with('poet')->where('slug', $key)->first()) && $p->poet
                ? ['title' => $p->title.' — '.$p->poet->name_en, 'text' => collect($p->stanzas())->map(fn ($s) => implode("\n", $s))->implode("\n\n"),
                    'url' => route('poetry.show', [$p->poet->slug, $p->slug]), 'notice' => ''] : null,
            'quote' => ctype_digit($key) && ($q = $pub(Quote::query())->find((int) $key))
                ? ['title' => 'Quote', 'text' => $q->body.($q->author ? "\n— ".$q->author : ''), 'url' => route('quotes.show', $q->id), 'notice' => ''] : null,
            'naat' => Schema::hasTable('naats') && ($n = $pub(Naat::query())->where('slug', $key)->first()) && trim((string) $n->lyrics) !== ''
                ? ['title' => $n->title, 'text' => $n->lyrics, 'url' => route('naats.show', $n->slug), 'notice' => ''] : null,
            default => null,
        };
    }

    private function dua(Dua $d): array
    {
        $tr = trim(implode("\n\n", array_filter([$d->translation_en, $d->translation_ur])));
        if ($d->quranVerses()) {
            return ['title' => $d->title, 'text' => $tr, 'url' => route('duas.show', $d->slug),
                'notice' => 'This dua is from the Quran, so a computer voice does not read its Arabic here. Only the translation is below; listen to a real Quran recitation on the dua page.'];
        }

        return ['title' => $d->title, 'text' => trim($d->arabic."\n\n".$tr), 'url' => route('duas.show', $d->slug),
            'notice' => 'Computer voices can mispronounce Arabic. For learning a dua, follow a teacher or a human recording.'];
    }

    /** A few published items to try, grouped by section (cached 10 min). */
    private function picks(): array
    {
        return Cache::remember('listen.picks-v1', 600, function () {
            $out = [];
            if (Schema::hasTable('duas')) {
                $out['Duas (Arabic + translation)'] = Dua::where('status', 'published')->orderBy('sort')->limit(40)->get()
                    ->reject(fn ($d) => $d->quranVerses())->take(4)
                    ->map(fn ($d) => [$d->title, 'dua:'.$d->slug])->values()->all();
            }
            if (Schema::hasTable('poems')) {
                $out['Urdu poetry'] = Poem::where('status', 'published')->with('poet')->latest('updated_at')->limit(4)->get()
                    ->filter->poet->map(fn ($p) => [$p->title.' — '.$p->poet->name_en, 'poem:'.$p->slug])->values()->all();
            }
            $out['Urdu corner'] = UrduContent::where('status', 'published')->latest('id')->limit(3)->get()
                ->map(fn ($u) => [Str::limit($u->body, 40), 'urdu:'.$u->slug])->all();
            $out['Messages & wishes'] = Message::where('status', 'published')->latest('id')->limit(3)->get()
                ->map(fn ($x) => [$x->title ?: Str::limit($x->body, 40), 'message:'.$x->slug])->all();

            return array_filter($out);
        });
    }
}
