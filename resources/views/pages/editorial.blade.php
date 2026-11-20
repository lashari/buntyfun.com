<x-layout title="Editorial Policy — How BuntyFun Content Is Written and Checked" description="How BuntyFun writes, checks, sources and corrects wishes, Islamic name meanings, quizzes, games and AI tool output."
          :breadcrumbs="['Editorial policy' => null]" :ads="false">
    <article class="mx-auto max-w-2xl px-4 py-10 prose-page">
        <h1 class="text-3xl font-bold text-slate-900">Editorial policy</h1>
        <p>This page explains how content on BuntyFun is produced and what you can rely on it for.</p>

        <h2>Wishes, messages and Urdu content</h2>
        <ul>
            <li>Texts are written for this site. Some first drafts come from AI writing tools.</li>
            <li>Before publishing, every draft is checked automatically for length, near-duplicates of existing items, links, keyword stuffing and stock AI phrasing. Urdu items must be written in Urdu script.</li>
            <li>Drafts that pass every check can be published automatically; anything flagged waits for a person. Editors can edit or unpublish any item at any time.</li>
            <li>Condolence messages never carry ads.</li>
        </ul>

        <h2>Islamic name meanings</h2>
        <ul>
            <li>Every name needs a source before it can be published, and the source is shown on the name page.</li>
            <li>Meanings come from linguistic references (Arabic roots and usage). Where references disagree or the origin is uncertain, we say so instead of picking one.</li>
            <li>Name meanings are not religious rulings. For questions about choosing a name in Islam, please consult a qualified scholar.</li>
            <li>Name entries are never published automatically. An AI tool may help prepare a draft, but a person must confirm it against the source before it goes live.</li>
        </ul>

        <h2>Quizzes and games</h2>
        <ul>
            <li>Trivia answers are checked against common reference works; each question shows a short explanation after you finish.</li>
            <li>Personality-style quizzes are for fun only and are not psychological tests.</li>
            <li>Games run entirely in your browser. Their rules and scoring are explained on each game page.</li>
        </ul>

        <h2>AI Fun Studio</h2>
        <p>The AI tools send the text you type to an AI model (currently Google Gemini) and show you the reply. Replies are generated on the spot, are not checked by a person, and can be wrong, repetitive or odd. They are not stored on BuntyFun or published on the site. Please do not type personal or sensitive information into them.</p>

        <h2>Images</h2>
        <p>We only publish images we created or have the right to use. If you believe an image or text on BuntyFun belongs to you, please <a href="{{ route('pages.contact') }}">contact us</a> and we will review it promptly.</p>

        <h2>Corrections</h2>
        <p>Found a mistake? Send the page link and the correction through our <a href="{{ route('pages.contact') }}">contact page</a>. Confirmed errors are fixed, and items that cannot be fixed are removed.</p>
    </article>
</x-layout>
