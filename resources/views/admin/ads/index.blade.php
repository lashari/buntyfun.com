<x-admin-layout title="Ads">
    <h1 class="text-2xl font-bold">Ads</h1>
    <p class="mt-1 max-w-3xl text-sm text-slate-600">
        Choose where ads may appear. Nothing shows until the AdSense publisher ID is in the server <code>.env</code>.
        Ads never show on noindex pages, policy pages, search, 404, quiz results, condolence messages or Listen to Text, whatever is set here.
        The only manual ad unit sits after the page content, above the footer, away from game controls, quiz answers, copy buttons and the menu.
    </p>

    <div class="mt-4 max-w-3xl rounded-xl border border-slate-200 bg-white p-4 text-sm">
        <h2 class="font-semibold">AdSense status</h2>
        <ul class="mt-2 space-y-1">
            <li>Publisher ID (<code>ADSENSE_CLIENT_ID</code>): {!! $clientId ? '<strong>set ✓</strong> '.e($clientId) : '<strong>not set</strong>. No ad script loads, /ads.txt returns 404.' !!}</li>
            <li>Manual ad unit (<code>ADSENSE_SLOT_ID</code>): {!! $slotId ? '<strong>set ✓</strong>' : '<strong>not set</strong>. Only Auto ads would show (Auto ads placement and devices are set in the AdSense account).' !!}</li>
            <li>Impressions and revenue: not available here yet. They come from the AdSense account once it is approved (planned for the analytics dashboard).</li>
        </ul>
    </div>

    @php $canEdit = auth()->user()->isSuperAdmin(); @endphp
    <form method="POST" action="{{ route('admin.ads.update') }}" class="mt-6 max-w-3xl space-y-6 rounded-xl border border-slate-200 bg-white p-4 text-sm">
        @csrf @method('PUT')
        <fieldset @disabled(! $canEdit) class="space-y-6">
            <label class="flex items-center gap-2 font-semibold">
                <input type="checkbox" name="ads_enabled" value="1" @checked($settings['enabled'])> Ads on (whole site)
            </label>

            <div>
                <h2 class="font-semibold">Sections</h2>
                <p class="text-xs text-slate-500">Off = no ad script and no ad unit on any page of that section.</p>
                <div class="mt-2 grid grid-cols-1 gap-1 sm:grid-cols-2">
                    @foreach ($sections as $key => [$label])
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="sections[{{ $key }}]" value="1" @checked($settings['sections'][$key] ?? false)> {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <h2 class="font-semibold">Devices (manual ad unit)</h2>
                <div class="mt-2 flex gap-6">
                    <label class="flex items-center gap-2"><input type="checkbox" name="ads_desktop" value="1" @checked($settings['desktop'])> Desktop and tablet</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="ads_mobile" value="1" @checked($settings['mobile'])> Mobile</label>
                </div>
                <p class="mt-1 text-xs text-slate-500">Both off = ads off everywhere.</p>
            </div>

            <div>
                <label class="font-semibold" for="ads_max_units">Most manual ad units per page</label>
                <select id="ads_max_units" name="ads_max_units" class="ml-2 rounded border-slate-300">
                    @foreach (range(0, \App\Services\Ads\AdControls::MAX_UNITS) as $n)
                        <option value="{{ $n }}" @selected($settings['max_units'] === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Today each page has one unit position, so 1 is the most used. 0 = Auto ads only.</p>
            </div>

            <div>
                <label class="font-semibold" for="excluded_paths">Pages with no ads</label>
                <p class="text-xs text-slate-500">One path per line, e.g. <code>/games/ludo</code> (also covers everything under it). Use for pages where an ad would sit too close to controls.</p>
                <textarea id="excluded_paths" name="excluded_paths" rows="6" class="mt-1 w-full rounded border-slate-300 font-mono text-xs">{{ old('excluded_paths', implode("\n", $settings['excluded'])) }}</textarea>
                @error('excluded_paths')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            @if ($canEdit)
                <button class="rounded bg-navy-600 px-4 py-2 font-semibold text-white hover:bg-navy-700">Save</button>
            @else
                <p class="text-xs text-slate-500">Only a super admin can change these.</p>
            @endif
        </fieldset>
    </form>
</x-admin-layout>
