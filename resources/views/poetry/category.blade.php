@php $title = $label[0]; @endphp
<x-layout :title="'Urdu '.$title.' — '.$label[1].' | Classic Poetry | BuntyFun'"
          :description="'Read classic Urdu '.Str::lower($title).' ('.$label[1].') by Ghalib, Iqbal, Mir and other great poets, in Nastaliq with copy and WhatsApp share.'"
          :breadcrumbs="['Urdu Poetry' => route('poetry.index'), $title => null]" :urduFont="true" :noindex="$poems->isEmpty() || request()->has('page')">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $isForm ? 'Urdu '.$title : $title }} <span class="font-urdu font-normal text-slate-500" lang="ur">{{ $label[1] }}</span></h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">
            @if ($isForm)
                @switch($key)
                    @case('ghazal') A ghazal is a poem of couplets (sher) that share a rhyme and refrain; each couplet stands on its own. @break
                    @case('nazm') A nazm is a poem on one theme that develops from start to finish, often with a title. @break
                    @case('rubai') A rubai is a four-line poem; lines one, two and four rhyme. @break
                    @case('qita') A qita is a short poem of a few couplets that together express one thought. @break
                    @case('naat') Naats praise the Prophet Muhammad ﷺ; hamd praises Allah. @break
                    @case('marsiya') A marsiya is an elegy, most often for the martyrs of Karbala. @break
                @endswitch
            @else
                Poems tagged {{ Str::lower(str_replace(' poetry', '', $title)) }} by our editors, from the classical poets in our collection.
            @endif
        </p>
        @include('poetry.partials.nav', ['active' => $key])
        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($poems as $poem) @include('poetry.partials.card') @empty <p class="text-slate-500">Poems for this section are being checked and will appear soon.</p> @endforelse
        </div>
        <div class="mt-8">{{ $poems->links() }}</div>
    </div>
</x-layout>
