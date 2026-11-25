<x-layout title="Urdu Poetry — Ghazals & Nazms of Ghalib, Iqbal, Mir & More | BuntyFun"
          description="Read classic Urdu poetry in Nastaliq: ghazals, nazms and rubaiyat by Mirza Ghalib, Allama Iqbal, Mir Taqi Mir, Dagh and other great poets. Poetry of the day, search, copy and share."
          :breadcrumbs="['Urdu Poetry' => null]" :urduFont="true" :noindex="$q !== ''">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Urdu Poetry <span class="font-urdu font-normal text-slate-500" lang="ur">اردو شاعری</span></h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">The great classical poets of Urdu, from Mir and Dard in the 18th century to Ghalib and Iqbal. Every poem here is in the public domain and shown with its source, in Nastaliq script. Copy a couplet, share it on WhatsApp or save it as a picture.</p>

        <form method="GET" class="mt-5 flex max-w-xl gap-2" role="search">
            <input type="search" name="q" value="{{ $q }}" placeholder="شعر تلاش کریں — search a word" class="font-urdu w-full rounded-lg border border-slate-300 px-3 py-2" dir="auto" aria-label="Search poetry">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 font-semibold text-white">Search</button>
        </form>
        @include('poetry.partials.nav')

        @if ($results !== null)
            <h2 class="mt-8 text-xl font-bold">Results for "{{ $q }}"</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($results as $poem) @include('poetry.partials.card') @empty <p class="text-slate-500">No poem found. Try a shorter word.</p> @endforelse
            </div>
        @endif

        @if ($today)
            <section class="mt-10 rounded-2xl bg-navy-700 p-6 text-white shadow">
                <h2 class="text-sm font-semibold uppercase tracking-wide opacity-80">Poetry of the day · {{ now()->format('M j') }}</h2>
                <a href="{{ route('poetry.show', [$today->poet->slug, $today->slug]) }}" class="mt-3 block">
                    @foreach (array_slice($today->stanzas(), 0, 1) as $st)
                        <p class="font-urdu text-center text-2xl leading-[2.4]" dir="rtl" lang="ur">@foreach ($st as $line){{ $line }}<br>@endforeach</p>
                    @endforeach
                    <p class="mt-2 text-center text-sm opacity-90">— {{ $today->poet->name_en }} <span class="font-urdu" lang="ur">({{ $today->poet->name_ur }})</span> · Read the full {{ Str::singular(Str::lower(\App\Models\Poem::FORMS[$today->form][0] ?? 'poem')) }}</p>
                </a>
            </section>
        @endif

        <h2 class="mt-10 text-xl font-bold">Poets</h2>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($poets as $p)
                <a href="{{ route('poetry.segment', $p->slug) }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md">
                    <span class="flex items-baseline justify-between gap-2"><span class="font-semibold">{{ $p->name_en }}</span><span class="font-urdu text-xl" lang="ur" dir="rtl">{{ $p->name_ur }}</span></span>
                    <span class="mt-1 block text-xs text-slate-500">{{ $p->years() }} · {{ $p->poems_count }} {{ Str::plural('poem', $p->poems_count) }}</span>
                </a>
            @endforeach
        </div>

        @if ($latest->isNotEmpty())
            <h2 class="mt-10 text-xl font-bold">Recently added</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($latest as $poem) @include('poetry.partials.card') @endforeach
            </div>
        @endif

        <section class="prose-page mt-12 max-w-3xl">
            <h2>About this collection</h2>
            <p>We publish only poetry that is out of copyright: the poets here died between 1785 and 1938. The texts come from Urdu Wikisource through the open <a href="https://github.com/anas-rashid/divan-data" rel="nofollow noopener" target="_blank">Divan</a> database (CC BY-SA 4.0), and every poem page links to its source. Mood labels (romantic, sad, Islamic, motivational, friendship) are added from the words of each poem and checked by our editors; tell us if one looks wrong.</p>
        </section>
        <x-explore group="urdu" />
    </div>
</x-layout>
