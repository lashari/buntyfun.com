@php
    $toneLabel = ['funny' => 'مزاحیہ', 'emotional' => 'جذباتی', 'short' => 'مختصر'];
    $forLabel = [
        'mother' => 'امی کے لیے', 'father' => 'ابو کے لیے', 'best_friend' => 'جگری دوست کے لیے', 'friend' => 'دوست کے لیے',
        'sister' => 'بہن کے لیے', 'brother' => 'بھائی کے لیے', 'husband' => 'شوہر کے لیے', 'wife' => 'بیوی کے لیے',
        'child' => 'بچوں کے لیے', 'colleague' => 'ساتھی کے لیے',
    ];
@endphp
<x-layout title="Birthday Wishes in Urdu — سالگرہ مبارک Messages for Family & Friends | BuntyFun"
          :description="$count.' original Urdu birthday wishes (سالگرہ مبارک) for Ammi, Abbu, friends, siblings, husband, wife and kids — funny, emotional and short, ready to copy or send on WhatsApp.'"
          :noindex="! $indexable" :urdu-font="true"
          :breadcrumbs="['Birthday Wishes' => route('birthday-messages.index'), 'In Urdu' => null]">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="text-3xl font-bold">Birthday Wishes in Urdu</h1>
        <p class="mt-2 font-urdu text-2xl leading-loose text-orange-700" dir="rtl" lang="ur">سالگرہ مبارک — اردو میں پیار بھرے پیغامات</p>
        <p class="mt-4 leading-relaxed text-slate-700">A birthday message in Urdu often means more than a longer one in English, especially for parents and grandparents. Every wish below was written in Urdu, not machine-translated. Pick who it is for, tap <strong>Copy</strong> or <strong>WhatsApp</strong>, and add one personal line of your own.</p>

        <nav class="mt-6 flex flex-wrap gap-2" aria-label="Jump to">
            @foreach ($wishes as $relationship => $items)
                <a href="#{{ \App\Models\BirthdayMessage::relationshipSlug($relationship) }}" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-sm hover:border-brand-300">
                    {{ \App\Content\BirthdayGuides::for($relationship)['label'] ?? Str::headline($relationship) }}
                    <span class="font-urdu" lang="ur">· {{ $forLabel[$relationship] ?? '' }}</span>
                </a>
            @endforeach
        </nav>

        @forelse ($wishes as $relationship => $items)
            <section class="mt-10" id="{{ \App\Models\BirthdayMessage::relationshipSlug($relationship) }}">
                <h2 class="text-xl font-bold">
                    Urdu birthday wishes for {{ Str::lower(\App\Content\BirthdayGuides::for($relationship)['label'] ?? Str::headline($relationship)) }}
                    <span class="font-urdu text-lg font-normal text-slate-500" lang="ur">({{ $forLabel[$relationship] ?? '' }})</span>
                </h2>
                <div class="mt-4 space-y-3">
                    @foreach ($items as $wish)
                        <div class="rounded-xl border border-slate-200 bg-white p-5" x-data="{ copied: false }">
                            <p class="font-urdu text-lg leading-loose text-slate-800" dir="rtl" lang="ur">{{ $wish->body }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                                <span class="rounded-full bg-orange-50 px-2.5 py-0.5 text-xs text-orange-700">{{ Str::headline($wish->tone) }} · <span class="font-urdu" lang="ur">{{ $toneLabel[$wish->tone] ?? '' }}</span></span>
                                <button type="button" class="rounded-full border border-slate-300 px-3 py-1 hover:bg-slate-50"
                                        @click="navigator.clipboard.writeText(@js($wish->body)); copied = true; setTimeout(() => copied = false, 2000)">
                                    <span x-show="!copied">Copy</span><span x-show="copied" x-cloak>Copied!</span>
                                </button>
                                <a href="https://wa.me/?text={{ rawurlencode($wish->body) }}" target="_blank" rel="noopener" class="rounded-full border border-slate-300 px-3 py-1 hover:bg-slate-50">WhatsApp</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if (\App\Content\BirthdayGuides::for($relationship) && in_array($relationship, \App\Models\BirthdayMessage::hubRelationships(), true))
                    <p class="mt-3 text-sm"><a href="{{ route('birthday-messages.relationship', \App\Models\BirthdayMessage::relationshipSlug($relationship)) }}" class="text-navy-600 underline">More wishes and tips for your {{ Str::lower(\App\Content\BirthdayGuides::for($relationship)['label']) }}</a></p>
                @endif
            </section>
        @empty
            <p class="mt-8 text-slate-500">Urdu wishes are coming soon.</p>
        @endforelse

        <section class="mt-12 rounded-xl bg-brand-50 p-6 prose-page">
            <h2 class="!mt-0">Tips for sending a birthday wish in Urdu</h2>
            <ul>
                <li>Start with the person's name or how you address them at home: Ammi, Abbu, Baji, Bhai Jaan. It makes a copied line sound like you.</li>
                <li>A short du'a at the end, such as <span class="font-urdu" lang="ur">اللہ آپ کو سلامت رکھے</span>, suits parents and elders better than a joke.</li>
                <li>On WhatsApp, Urdu text shows right-to-left automatically. If it looks jumbled, send the wish on its own, without English in the same message.</li>
                <li>For a card, the emotional wishes read best; for a family group chat, the short ones keep it light.</li>
            </ul>
        </section>
        <p class="mt-8 text-sm text-slate-500">Looking for English? Browse all <a href="{{ route('birthday-messages.index') }}" class="text-navy-600 underline">birthday wishes</a>, or read more <a href="{{ route('urdu-content.index') }}" class="text-navy-600 underline">Urdu greetings and quotes</a>.</p>
    </div>
</x-layout>
