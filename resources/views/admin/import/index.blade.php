<x-admin-layout title="Import">
    <h1 class="text-2xl font-bold">Import content</h1>
    <p class="mt-1 text-sm text-slate-500">Pull content from websites whose terms allow re-use. Everything is saved as <b>In review</b> — nothing goes public until you publish it.</p>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.import.store') }}" class="mt-6 space-y-6 rounded-xl border border-slate-200 bg-white p-6">
        @csrf

        <div>
            <h2 class="font-semibold">1. What do you want to import?</h2>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                @foreach ($types as $key => $label)
                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm {{ $key === 'wallpaper' ? '' : 'opacity-50' }}">
                        <input type="radio" name="content_type" value="{{ $key }}" @checked(old('content_type', 'wallpaper') === $key) @disabled($key !== 'wallpaper')>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <h2 class="font-semibold">2. Which website?</h2>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                @foreach ($sources as $key => $source)
                    @php($problem = $source->setupProblem())
                    <label class="block rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <span class="flex items-center gap-2">
                            <input type="radio" name="source" value="{{ $key }}" @checked(old('source', 'nasa') === $key)>
                            <b>{{ $source->label() }}</b>
                            <a href="{{ $source->website() }}" target="_blank" rel="noopener" class="text-xs text-slate-500 hover:underline">{{ parse_url($source->website(), PHP_URL_HOST) }}</a>
                        </span>
                        @if ($problem)
                            <span class="mt-1 block text-xs text-amber-700">⚠ {{ $problem }}</span>
                        @else
                            <span class="mt-1 block text-xs text-emerald-700">✓ Ready</span>
                        @endif
                    </label>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-slate-500">Not available on purpose: Unsplash, Pexels, Pixabay (their licences ban wallpaper sites), birthdaymsgs.com, wallpapers.com, 4kwallpapers.com (copyrighted / unknown owners; copied text hurts Google/AdSense). Wikimedia: only public domain, CC0, CC BY and CC BY-SA files are saved (with credit).</p>
        </div>

        <div>
            <h2 class="font-semibold">3. Search settings</h2>
            <div class="mt-2 grid gap-4 sm:grid-cols-3">
                <label class="text-sm">Search words
                    <input type="text" name="query" value="{{ old('query', 'galaxy') }}" required placeholder="e.g. galaxy, mountains, mosque"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                </label>
                <label class="text-sm">Save into category
                    <input type="text" name="category" value="{{ old('category', 'space') }}" required list="import-categories"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    <datalist id="import-categories">
                        @foreach ($categories as $category)<option value="{{ $category }}">@endforeach
                    </datalist>
                </label>
                <label class="text-sm">How many to save (max 100)
                    <input type="number" name="target_count" value="{{ old('target_count', 20) }}" min="1" max="100" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                </label>
                <label class="text-sm">Orientation
                    <select name="orientation" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option value="any" @selected(old('orientation') === 'any')>Any</option>
                        <option value="desktop" @selected(old('orientation') === 'desktop')>Desktop (landscape)</option>
                        <option value="mobile" @selected(old('orientation') === 'mobile')>Mobile (portrait)</option>
                    </select>
                </label>
                <label class="text-sm">Minimum size (long side, px)
                    <input type="number" name="min_width" value="{{ old('min_width', 1280) }}" min="0" max="8000" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                </label>
                <label class="text-sm">Start from result page
                    <input type="number" name="page" value="{{ old('page', 1) }}" min="1" max="100"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                </label>
            </div>
            <label class="mt-3 flex items-center gap-2 text-sm">
                <input type="hidden" name="quality_only" value="0">
                <input type="checkbox" name="quality_only" value="1" @checked(old('quality_only', '1') === '1')>
                Best photos only (Wikimedia "Quality images": reviewed, sharp, no charts or diagrams)
            </label>
            <p class="mt-2 text-xs text-slate-500">Pictures already imported are skipped automatically, so running the same search again only adds new ones.</p>
        </div>

        <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700">Start import</button>
    </form>

    <h2 class="mt-8 text-lg font-semibold">Recent imports</h2>
    <div class="mt-2 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr><th class="px-3 py-2">#</th><th class="px-3 py-2">When</th><th class="px-3 py-2">Website</th><th class="px-3 py-2">Search</th><th class="px-3 py-2">Category</th><th class="px-3 py-2">Saved</th><th class="px-3 py-2">Skipped</th><th class="px-3 py-2">Failed</th><th class="px-3 py-2">Status</th></tr>
            </thead>
            <tbody>
                @forelse ($runs as $run)
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2"><a href="{{ route('admin.import.show', $run) }}" class="text-navy-600 hover:underline">{{ $run->id }}</a></td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $run->created_at->format('m-d-Y H:i') }}</td>
                        <td class="px-3 py-2">{{ Str::headline($run->source) }}</td>
                        <td class="px-3 py-2">{{ $run->query }}</td>
                        <td class="px-3 py-2">{{ $run->category }}</td>
                        <td class="px-3 py-2 font-semibold text-emerald-700">{{ $run->saved }}</td>
                        <td class="px-3 py-2">{{ $run->skipped }}</td>
                        <td class="px-3 py-2 {{ $run->failed ? 'text-red-600' : '' }}">{{ $run->failed }}</td>
                        <td class="px-3 py-2">{{ Str::headline($run->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-3 py-4 text-slate-500">No imports yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
