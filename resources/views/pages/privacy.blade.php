@php
    $email = config('site.contact_email');
    $operator = config('site.operator');
    $updated = \Illuminate\Support\Carbon::parse(config('site.policies_updated'))->format('m-d-Y');
@endphp
<x-layout title="Privacy Policy — BuntyFun" description="What BuntyFun collects, which cookies Google Analytics and Google AdSense use, how consent works, and how to contact us about your data."
          :breadcrumbs="['Privacy policy' => null]" :ads="false">
    <article class="mx-auto max-w-2xl px-4 py-10 prose-page">
        <h1 class="text-3xl font-bold text-slate-900">Privacy policy</h1>
        <p class="text-sm text-slate-500">Last updated: {{ $updated }}</p>
        <p>This policy explains what information BuntyFun.com ("BuntyFun", "we") collects when you use the site, why, and the choices you have. @if ($operator) BuntyFun is operated by {{ $operator }}. @endif</p>

        <h2>1. Information we store ourselves</h2>
        <p>You do not need an account to use BuntyFun. We store only what the site needs to work:</p>
        <table>
            <tr><th>What</th><th>Why</th><th>Kept</th></tr>
            <tr><td>Session cookie and security (CSRF) token, with the IP address and browser type of the session</td><td>To keep forms and buttons working and to protect against abuse</td><td>About 2 hours after your last visit</td></tr>
            <tr><td>A one-way hash of your IP address when you use an AI tool</td><td>To enforce the free daily limit per tool. We do not store the raw IP for this.</td><td>Until deleted by us; only the hash and the time are kept</td></tr>
            <tr><td>Anonymous page-view, copy and share counts per item (no IP, no cookie id)</td><td>To see which wishes, names and games are useful</td><td>Aggregated</td></tr>
            <tr><td>Quiz answers you submit</td><td>To show and share your result page</td><td>Until deleted by us</td></tr>
            <tr><td>Game scores and the optional nickname you type</td><td>To show scores</td><td>Until deleted by us</td></tr>
            <tr><td>Server logs (IP address, time, page requested, browser)</td><td>Security and troubleshooting; kept by our hosting provider</td><td>Limited period set by the host</td></tr>
        </table>
        <p>Do not type personal or sensitive information into quizzes, nicknames or AI tools.</p>

        <h2>2. AI tools</h2>
        <p>When you press Generate in the AI Fun Studio, the text you typed is sent to our AI provider (currently Google Gemini, operated by Google) to produce a reply. BuntyFun does not save your text or the reply. Google processes the request under its own terms and privacy policy.</p>

        <h2>3. Google Analytics</h2>
        <p>We use Google Analytics 4 to understand how many people visit and which pages they use. Google Analytics sets cookies (such as <code>_ga</code>) and receives your IP address, device and browser information and the pages you view. We use the reports in aggregate; we do not use them to identify you. You can opt out with the <a href="https://tools.google.com/dlpage/gaoptout" rel="noopener" target="_blank">Google Analytics opt-out browser add-on</a>.</p>

        <h2>4. Advertising (Google AdSense)</h2>
        <p>BuntyFun may show ads served by Google AdSense. When ads are shown:</p>
        <ul>
            <li>Third-party vendors, including Google, use cookies to serve ads based on your prior visits to this website or other websites.</li>
            <li>Google's use of advertising cookies enables it and its partners to serve ads to you based on your visits to this site and/or other sites on the Internet.</li>
            <li>You may opt out of personalised advertising by visiting Google <a href="https://adssettings.google.com" rel="noopener" target="_blank">Ads Settings</a>. You can also opt out of some third-party vendors' use of cookies for personalised advertising at <a href="https://www.aboutads.info/choices/" rel="noopener" target="_blank">www.aboutads.info</a>.</li>
            <li>Other ad vendors and ad networks approved by Google may also serve ads on BuntyFun. You can see how Google uses information from sites that use its services at <a href="https://policies.google.com/technologies/partner-sites" rel="noopener" target="_blank">How Google uses information from sites or apps that use our services</a>.</li>
        </ul>
        <p>We do not show ads on condolence messages, search results, quiz result pages or our policy pages.</p>

        <h2>5. Consent (EEA, UK, Switzerland and other regions)</h2>
        <p>If you visit from the European Economic Area, the United Kingdom or Switzerland, analytics and advertising cookies stay off until you make a choice in the consent message, which is provided by Google's certified consent tool. Until then, Google receives only limited, cookieless signals as described in Google Consent Mode. Where a consent or privacy message is shown, you can change your choice at any time with the "Privacy settings" link in the footer. Visitors in US states with privacy laws can use the same link, where shown, to opt out of the sale or sharing of personal information for advertising.</p>

        <h2>6. Sharing</h2>
        <p>We do not sell your personal information. Data is shared only with the service providers above (hosting, Google Analytics, Google AdSense, Google Gemini) so the site can work, or when the law requires it. When you press a WhatsApp or share button, you leave BuntyFun and that service's privacy policy applies.</p>

        <h2>7. Children</h2>
        <p>BuntyFun is a general-audience website and is not directed at children under 13. We do not knowingly collect personal information from children. If you believe a child has sent us personal information, please contact us and we will delete it.</p>

        <h2>8. Your rights</h2>
        <p>Depending on where you live, you may have the right to access, correct or delete personal information we hold about you, or to object to its use. Because we do not run accounts, we may need details such as the quiz or nickname you used to find it. You can also control cookies through your browser settings.</p>

        <h2>9. Changes</h2>
        <p>We will update this page when our practices change and change the date at the top.</p>

        <h2>10. Contact</h2>
        <p>
            @if ($email) Questions about privacy: <a href="mailto:{{ $email }}">{{ $email }}</a>. @else Questions about privacy: see our <a href="{{ route('pages.contact') }}">contact page</a>. @endif
        </p>
    </article>
</x-layout>
