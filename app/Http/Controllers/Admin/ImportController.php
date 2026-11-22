<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ImportRun;
use App\Models\Wallpaper;
use App\Services\Import\ImportRunner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ImportController extends Controller
{
    /** Content types shown in the picker. Only those with a source are selectable today. */
    public const TYPES = [
        'wallpaper' => 'Wallpapers',
        'quote' => 'Quotes (Islamic, best wishes) — next',
        'wish' => 'Birthday / anniversary / condolence — next',
        'name' => 'Islamic names — next',
    ];

    public function index()
    {
        return view('admin.import.index', [
            'types' => self::TYPES,
            'sources' => ImportRunner::sources(),
            'categories' => Wallpaper::distinct()->orderBy('category')->pluck('category')
                ->merge(['space', 'nature', 'landscapes', 'abstract', 'animals', 'islamic', 'technology'])->unique()->values(),
            'runs' => ImportRun::latest()->limit(15)->get(),
        ]);
    }

    public function store(Request $request, ImportRunner $runner)
    {
        $data = $request->validate([
            'content_type' => ['required', Rule::in(['wallpaper'])],
            'source' => ['required', Rule::in(array_keys(ImportRunner::sources()))],
            'query' => ['required', 'string', 'min:2', 'max:200'],
            'category' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/'],
            'target_count' => ['required', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'orientation' => ['required', 'in:any,desktop,mobile'],
            'min_width' => ['required', 'integer', 'min:0', 'max:8000'],
            'quality_only' => ['nullable', 'boolean'],
        ], ['category.regex' => 'Category: lowercase letters, numbers and dashes only (e.g. space).']);

        $source = ImportRunner::source($data['source']);
        if ($problem = $source->setupProblem()) {
            return back()->withInput()->withErrors(['source' => $problem]);
        }

        $run = ImportRun::create([
            'user_id' => $request->user()->id,
            'content_type' => $data['content_type'],
            'source' => $data['source'],
            'query' => $data['query'],
            'category' => $data['category'],
            'target_count' => $data['target_count'],
            'page' => $data['page'] ?? 1,
            'options' => [
                'orientation' => $data['orientation'],
                'min_width' => $data['min_width'],
                'quality_only' => $request->boolean('quality_only'),
                'status' => 'in_review', // never straight to public; publish is a separate step
            ],
        ]);
        $runner->start($run);

        return redirect()->route('admin.import.show', $run);
    }

    public function show(ImportRun $run)
    {
        return view('admin.import.show', [
            'run' => $run,
            'source' => ImportRunner::source($run->source),
            'items' => $run->items()->latest('id')->limit(200)->get(),
            'wallpapers' => $run->savedWallpapers()->latest()->limit(60)->get(),
            'unpublished' => $run->savedWallpapers()->where('status', '!=', 'published')->count(),
        ]);
    }

    public function step(ImportRun $run, ImportRunner $runner)
    {
        $run = $runner->step($run);
        $recent = $run->items()->latest('id')->limit(10)->get(['source_key', 'title', 'status', 'message']);

        return response()->json([...$run->progress(), 'recent' => $recent]);
    }

    /** Make missing thumbnails, a few per call (the page/JS or curl repeats until left = 0). */
    public function thumbs(\App\Services\Import\ThumbnailMaker $maker)
    {
        $started = microtime(true);
        $made = 0;
        $errors = [];
        $tried = array_map('intval', (array) session('thumb_tried', []));
        foreach (Wallpaper::where('file_path', '!=', '')->whereNull('remote_url')->whereNotIn('id', $tried)->orderByDesc('id')->cursor() as $w) {
            if (microtime(true) - $started > 20) {
                break;
            }
            if (\App\Services\Import\ThumbnailMaker::has($w)) {
                continue;
            }
            $tried[] = $w->id;
            if ($error = $maker->make($w)) {
                $errors[$w->id] = $error;
            } else {
                $made++;
            }
        }
        session(['thumb_tried' => $tried]);
        $left = Wallpaper::where('file_path', '!=', '')->whereNull('remote_url')->whereNotIn('id', $tried)->get()
            ->reject(fn ($w) => \App\Services\Import\ThumbnailMaker::has($w))->count();

        return response()->json(['made' => $made, 'left' => $left, 'errors' => $errors]);
    }

    public function stop(ImportRun $run)
    {
        if ($run->status === 'running') {
            $run->update(['status' => 'done', 'message' => 'Stopped by admin.']);
        }

        return redirect()->route('admin.import.show', $run);
    }

    public function publish(Request $request, ImportRun $run)
    {
        abort_unless($request->user()->canPublish(), 403);
        $count = 0;
        foreach ($run->savedWallpapers()->where('status', '!=', 'published')->get() as $wallpaper) {
            $wallpaper->update(['status' => 'published']);
            AuditLog::record($request->user(), 'published', 'wallpaper', $wallpaper->id);
            $count++;
        }

        return back()->with('status', "Published {$count} wallpapers.");
    }

    /** Remove what this import added (only items not yet published). */
    public function undo(Request $request, ImportRun $run)
    {
        abort_unless($request->user()->canPublish(), 403);
        $count = 0;
        foreach ($run->savedWallpapers()->where('status', '!=', 'published')->get() as $wallpaper) {
            if ($wallpaper->file_path !== '') {
                Storage::disk('public')->delete($wallpaper->file_path);
            }
            \App\Services\Import\ThumbnailMaker::forget($wallpaper);
            AuditLog::record($request->user(), 'deleted', 'wallpaper', $wallpaper->id);
            $wallpaper->delete();
            $count++;
        }
        $left = $run->savedWallpapers()->count();
        if ($left === 0) {
            $run->update(['status' => 'undone', 'message' => "Removed {$count} imported wallpapers."]);
        }

        return back()->with('status', "Removed {$count} not-yet-published wallpapers".($left ? " ({$left} published ones kept)." : '.'));
    }

    public function csv(ImportRun $run)
    {
        $wallpapers = $run->savedWallpapers()->get();

        return response()->streamDownload(function () use ($wallpapers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // Excel opens UTF-8 correctly with a BOM
            fputcsv($out, self::CSV_HEADER);
            foreach ($wallpapers as $w) {
                fputcsv($out, $this->csvRow($w));
            }
            fclose($out);
        }, "import-{$run->id}-{$run->source}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function zip(ImportRun $run)
    {
        abort_unless(class_exists(\ZipArchive::class), 500, 'ZipArchive not available on this server.');
        $wallpapers = $run->savedWallpapers()->get();

        $tmp = tempnam(sys_get_temp_dir(), 'imp');
        $zip = new \ZipArchive;
        $zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $csv = fopen('php://temp', 'w+');
        fwrite($csv, "\xEF\xBB\xBF");
        fputcsv($csv, [...self::CSV_HEADER, 'file_in_zip']);
        foreach ($wallpapers as $w) {
            $inZip = '';
            $disk = Storage::disk('public');
            if ($w->file_path !== '' && $disk->exists($w->file_path)) {
                $inZip = 'images/'.$w->slug.'.'.pathinfo($w->file_path, PATHINFO_EXTENSION);
                $zip->addFile($disk->path($w->file_path), $inZip);
            }
            fputcsv($csv, [...$this->csvRow($w), $inZip]);
        }
        rewind($csv);
        $zip->addFromString('wallpapers.csv', stream_get_contents($csv));
        $zip->addFromString('README.txt', $run->source === 'unsplash'
            ? "Unsplash photos are listed with their image_url only.\nUnsplash API terms do not allow re-hosting copies, so no image files are included.\n"
            : "NASA images: public domain. Keep the attribution column when re-using.\n");
        $zip->close();

        return response()->download($tmp, "import-{$run->id}-{$run->source}.zip")->deleteFileAfterSend();
    }

    private const CSV_HEADER = ['id', 'title', 'category', 'orientation', 'width', 'height', 'license',
        'attribution', 'source_url', 'image_url', 'status', 'source_key'];

    private function csvRow(Wallpaper $w): array
    {
        return [$w->id, $w->title, $w->category, $w->orientation, $w->width, $w->height, $w->license,
            $w->attribution, $w->source_url, $w->url(), $w->status, $w->source_key];
    }
}
