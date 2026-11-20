@php $operator = config('site.operator'); @endphp
<x-layout title="About BuntyFun — What We Make and How" description="What BuntyFun is, who it is for, how our wishes, names, games, quizzes and AI tools are made, and how to reach us."
          :breadcrumbs="['About' => null]" :ads="false">
    <article class="mx-auto max-w-2xl px-4 py-10 prose-page">
        <h1 class="text-3xl font-bold text-slate-900">About BuntyFun</h1>
        <p>BuntyFun is a free, light-hearted website for everyday moments: a birthday wish you can copy in a few seconds, a short message for Eid or a friend, the meaning of an Islamic name, a quick browser game or quiz, and a few small AI tools for jokes, captions and greetings. Everything works in the browser on a phone or a computer, with no app and no account.</p>
        @if ($operator)
            <p>BuntyFun is run by {{ $operator }}.</p>
        @endif

        <h2>What you will find here</h2>
        <ul>
            <li><a href="{{ route('birthday-messages.index') }}">Birthday wishes</a> grouped by relationship (mother, best friend, colleague…) and tone (funny, emotional, short), with tips on how to personalise them.</li>
            <li><a href="{{ route('name-entries.index') }}">Islamic names</a> with Arabic and Urdu spelling, gender and meaning, and the source each meaning was checked against.</li>
            <li><a href="{{ route('games.index') }}">Browser games</a> with rules, scoring and tips on each page.</li>
            <li><a href="{{ route('quizzes.index') }}">Quizzes</a> that explain the right answers after you finish.</li>
            <li><a href="{{ route('urdu-content.index') }}">Urdu content</a>: greetings, friendship lines, quotes and clean jokes.</li>
            <li><a href="{{ route('ai-tools.index') }}">AI Fun Studio</a>: small generators for jokes, captions, nicknames and greetings, with clear limits.</li>
        </ul>

        <h2>How the content is made</h2>
        <p>Wishes, messages and Urdu lines are written for BuntyFun. Some first drafts are produced with AI writing tools; every draft then goes through automatic checks (length, duplicates against what is already on the site, links, spammy wording). Drafts that pass every check may be published automatically; the rest wait for an editor, and editors can correct or remove any published item. If something reads badly or is wrong, please tell us and we will fix or remove it.</p>
        <p>Name meanings are researched against published references, and each name page shows its source. Where references disagree, the page says so. Read the full <a href="{{ route('pages.editorial') }}">editorial policy</a>.</p>

        <h2>What we do not do</h2>
        <ul>
            <li>We do not ask you to sign up, and we do not sell personal data.</li>
            <li>We do not publish religious rulings. Name pages explain meanings only; for religious questions please ask a qualified scholar.</li>
            <li>We do not show made-up reviews, follower counts or popularity numbers.</li>
        </ul>

        <h2>Advertising</h2>
        <p>BuntyFun is free to use. We may show ads from Google AdSense to cover hosting costs. Ads are kept away from pages where they would be out of place, such as condolence messages. How ads and cookies work is explained in our <a href="{{ route('pages.privacy') }}">privacy policy</a>.</p>

        <h2>Contact</h2>
        <p>Corrections, takedown requests and questions are welcome on our <a href="{{ route('pages.contact') }}">contact page</a>.</p>
    </article>
</x-layout>
