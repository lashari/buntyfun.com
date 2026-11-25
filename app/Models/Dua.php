<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dua extends Model
{
    use \App\Models\Concerns\HasContentMeta;

    public const CATEGORIES = [
        'daily' => 'Daily life', 'morning-evening' => 'Morning & evening', 'food' => 'Food & drink', 'sleep' => 'Sleep',
        'travel' => 'Travel', 'quran' => 'From the Quran', 'parents' => 'Parents & family', 'forgiveness' => 'Forgiveness', 'knowledge' => 'Knowledge & study',
        'prayer' => 'Prayer (salah)', 'protection' => 'Protection', 'distress' => 'Worry & distress', 'illness' => 'Illness & visiting the sick',
    ];

    protected $fillable = ['slug', 'title', 'category', 'arabic', 'transliteration', 'translation_en', 'translation_ur', 'reference', 'source_url', 'grading', 'grading_by', 'verification', 'sort', 'status'];

    /** Category pages people search for, in menu order. */
    public const CATEGORY_SLUGS = ['morning-evening', 'sleep', 'travel', 'prayer', 'protection', 'food', 'daily', 'distress', 'illness', 'forgiveness', 'parents', 'knowledge', 'quran'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function audio()
    {
        return $this->hasOne(AudioTrack::class, 'ref_id')->where('kind', 'dua')->where('status', 'published')->latest('id');
    }

    /** [[surah, ayah], …] when the dua is taken from the Quran ("Quran 2:255", "Quran 112:1-4, 113:1-5; …"). */
    public function quranVerses(): array
    {
        if (! preg_match('/^Quran ([0-9:,\- ]+)/', (string) $this->reference, $m)) {
            return [];
        }
        $out = [];
        foreach (explode(',', $m[1]) as $part) {
            if (preg_match('/(\d+):(\d+)(?:-(\d+))?/', $part, $r)) {
                foreach (range((int) $r[2], (int) (($r[3] ?? "") !== "" ? $r[3] : $r[2])) as $a) {
                    $out[] = [(int) $r[1], $a];
                }
            }
        }

        return array_slice($out, 0, 20);
    }

    /**
     * What the Listen button plays, for a list of duas in one query:
     * quran = Quran recitation of the source verses (global ayah numbers),
     * file = a reviewed recording uploaded by an editor, voice = device voice fallback.
     */
    public static function withListen(\Illuminate\Support\Collection $duas): \Illuminate\Support\Collection
    {
        $pairs = $duas->flatMap(fn ($d) => $d->quranVerses());
        $ids = collect();
        if ($pairs->isNotEmpty() && \Illuminate\Support\Facades\Schema::hasTable('quran_ayahs')) {
            $ids = QuranAyah::where(function ($q) use ($pairs) {
                foreach ($pairs as [$su, $ay]) {
                    $q->orWhere(fn ($w) => $w->where('surah', $su)->where('ayah', $ay));
                }
            })->get(['id', 'surah', 'ayah'])->keyBy(fn ($a) => $a->surah.':'.$a->ayah);
        }
        $tracks = \Illuminate\Support\Facades\Schema::hasTable('audio_tracks')
            ? AudioTrack::where('kind', 'dua')->where('status', 'published')->whereIn('ref_id', $duas->pluck('id'))->latest('id')->get()->unique('ref_id')->keyBy('ref_id')
            : collect();

        return $duas->each(function ($d) use ($ids, $tracks) {
            $verses = collect($d->quranVerses())->map(fn ($p) => $ids[$p[0].':'.$p[1]] ?? null)->filter()
                ->map(fn ($a) => ['g' => $a->id, 's' => $a->surah, 'a' => $a->ayah])->values()->all();
            $t = $tracks[$d->id] ?? null;
            $d->listen = $verses ? ['type' => 'quran', 'verses' => $verses]
                : ($t ? ['type' => 'file', 'src' => $t->src(), 'speaker' => $t->speaker] : ['type' => 'voice']);
        });
    }
}
