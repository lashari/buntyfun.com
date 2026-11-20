@php
    $operator = config('site.operator');
    $country = config('site.country');
    $updated = \Illuminate\Support\Carbon::parse(config('site.policies_updated'))->format('m-d-Y');
@endphp
<x-layout title="Terms of Use — BuntyFun" description="The rules for using BuntyFun: personal use of wishes and messages, AI tool limits, acceptable use, disclaimers and contact."
          :breadcrumbs="['Terms of use' => null]" :ads="false">
    <article class="mx-auto max-w-2xl px-4 py-10 prose-page">
        <h1 class="text-3xl font-bold">Terms of use</h1>
        <p class="text-sm text-slate-500">Last updated: {{ $updated }}</p>
        <p>By using BuntyFun.com you agree to these terms. If you do not agree, please do not use the site. @if ($operator) BuntyFun is operated by {{ $operator }}. @endif</p>

        <h2>1. Using our content</h2>
        <ul>
            <li>You may copy and send wishes, messages and Urdu texts to friends and family, and post them on your own social accounts. That is what they are for.</li>
            <li>You may not republish our content in bulk, scrape the site, or sell our content or present it as your own collection.</li>
            <li>The BuntyFun name, design and code remain ours.</li>
        </ul>

        <h2>2. AI tools</h2>
        <ul>
            <li>AI replies are generated automatically and are not reviewed by a person. They may be inaccurate, repetitive or unsuitable; use your own judgement before sharing them.</li>
            <li>Each tool has a free daily limit per visitor.</li>
            <li>Do not use the tools to create content that harasses, threatens or sexualises anyone, or that is illegal. Roasts are meant to be friendly.</li>
            <li>Do not type personal or sensitive information.</li>
        </ul>

        <h2>3. Acceptable use</h2>
        <p>Do not try to break, overload or bypass limits on the site, submit automated requests, or use it for spam.</p>

        <h2>4. Names, quizzes and information</h2>
        <p>Name meanings, quiz explanations and other information are provided in good faith for general interest. They are not professional, legal or religious advice. For religious questions please consult a qualified scholar.</p>

        <h2>5. Links and third parties</h2>
        <p>Share buttons and links can take you to other websites (for example WhatsApp). We are not responsible for their content or policies. Ads, where shown, are provided by Google and its partners.</p>

        <h2>6. No warranty</h2>
        <p>BuntyFun is provided free and "as is". We do our best to keep it accurate and available, but we do not guarantee that it will always be error-free or online. To the extent the law allows, we are not liable for losses arising from use of the site.</p>

        <h2>7. Changes</h2>
        <p>We may update these terms; the date at the top shows the latest version.</p>
        @if ($country)
            <h2>8. Governing law</h2>
            <p>These terms are governed by the laws of {{ $country }}.</p>
        @endif

        <h2>Contact</h2>
        <p>Questions about these terms: see our <a href="{{ route('pages.contact') }}">contact page</a>.</p>
    </article>
</x-layout>
