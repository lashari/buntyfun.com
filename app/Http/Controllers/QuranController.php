<?php

namespace App\Http\Controllers;

use App\Models\QuranAyah;
use App\Models\QuranReciter;
use App\Models\QuranSurah;
use App\Models\QuranTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Quran library: surah index, surah reader (Tanzil Uthmani text, published translations,
 * per-ayah audio streamed unaltered from the reciter's source) and a text search.
 */
class QuranController extends Controller
{
    private function ready(): bool
    {
        return Schema::hasTable('quran_ayahs') && QuranSurah::count() === 114;
    }

    public function index()
    {
        abort_unless($this->ready(), 404);

        return view('quran.index', [
            'surahs' => QuranSurah::orderBy('number')->get(),
            'reciters' => QuranReciter::live(),
        ]);
    }

    public function surah(string $surah)
    {
        abort_unless($this->ready(), 404);
        $number = (int) $surah;
        $s = QuranSurah::find($number) ?? abort(404);
        if ($surah !== $s->getRouteKey()) {
            return redirect($s->url(), 301);
        }

        $translations = QuranTranslation::live();
        $ayahs = QuranAyah::where('surah', $number)->orderBy('ayah')->get(['id', 'ayah', 'juz', 'text_uthmani']);
        $texts = $translations->isEmpty() ? collect() : DB::table('quran_translation_texts')
            ->whereIn('translation_id', $translations->pluck('id'))
            ->whereBetween('ayah_id', [$ayahs->first()->id, $ayahs->last()->id])
            ->get()->groupBy('ayah_id');

        return view('quran.surah', [
            'surah' => $s,
            'ayahs' => $ayahs,
            'texts' => $texts,
            'translations' => $translations,
            'reciters' => QuranReciter::live(),
            'prev' => QuranSurah::find($number - 1),
            'next' => QuranSurah::find($number + 1),
        ]);
    }

    /** Arabic (letters only, diacritics ignored) or translation search. noindex, max 50 results. */
    public function search(Request $request)
    {
        abort_unless($this->ready(), 404);
        $q = trim(mb_substr((string) $request->query('q'), 0, 80));
        $results = collect();
        if (mb_strlen($q) >= 2) {
            $like = '%'.addcslashes(self::normalizeArabic($q), '%_\\').'%';
            $ids = QuranTranslation::where('status', 'published')->pluck('id');
            $hits = DB::table('quran_translation_texts')->whereIn('translation_id', $ids)
                ->where('text', 'like', '%'.addcslashes($q, '%_\\').'%')->orderBy('ayah_id')->limit(50)->pluck('ayah_id');
            if (preg_match('/\p{Arabic}/u', $q)) { // Arabic letters: also the Quran text itself (Urdu is Arabic script too)
                $hits = QuranAyah::where('text_simple', 'like', $like)->orderBy('id')->limit(50)->pluck('id')->merge($hits);
            }
            $results = QuranAyah::whereIn('id', $hits->unique()->take(50))->orderBy('id')->get();
        }
        $surahs = QuranSurah::whereIn('number', $results->pluck('surah')->unique())->get()->keyBy('number');
        $texts = $results->isEmpty() ? collect() : DB::table('quran_translation_texts')
            ->whereIn('translation_id', QuranTranslation::where('status', 'published')->pluck('id'))
            ->whereIn('ayah_id', $results->pluck('id'))->get()->groupBy('ayah_id');
        // also match surah names
        $nameHits = mb_strlen($q) >= 2 ? QuranSurah::where('name_en', 'like', '%'.addcslashes($q, '%_\\').'%')
            ->orWhere('name_meaning', 'like', '%'.addcslashes($q, '%_\\').'%')->orWhere('name_ar', 'like', '%'.addcslashes($q, '%_\\').'%')->orderBy('number')->get() : collect();

        return view('quran.search', ['q' => $q, 'results' => $results, 'surahs' => $surahs, 'texts' => $texts, 'nameHits' => $nameHits]);
    }

    /** Same folding as the import: no diacritics/tatweel, one alif/ya/ha form, no hamza. */
    public static function normalizeArabic(string $s): string
    {
        $s = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u', '', $s);
        $s = strtr($s, ['ٱ' => 'ا', 'إ' => 'ا', 'أ' => 'ا', 'آ' => 'ا', 'ى' => 'ي', 'ئ' => 'ي', 'ؤ' => 'و', 'ة' => 'ه', 'ء' => '']);

        return trim(preg_replace('/\s+/u', ' ', $s));
    }
}
