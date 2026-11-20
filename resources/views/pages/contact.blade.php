@php $email = config('site.contact_email'); @endphp
<x-layout title="Contact BuntyFun" description="How to contact BuntyFun for corrections, content removal requests, privacy questions or feedback."
          :breadcrumbs="['Contact' => null]" :ads="false">
    <article class="mx-auto max-w-2xl px-4 py-10 prose-page">
        <h1 class="text-3xl font-bold">Contact us</h1>
        @if ($email)
            <p>Email: <a href="mailto:{{ $email }}">{{ $email }}</a></p>
            <p>We read every message and usually reply within a few working days.</p>
        @else
            <p>Our contact address is being set up. Please check back shortly.</p>
        @endif

        <h2>What to include</h2>
        <ul>
            <li><strong>Corrections</strong> (for example a name meaning or spelling): the page link, what is wrong, and a source if you have one.</li>
            <li><strong>Copyright or removal requests</strong>: the page link, the content concerned, and how you are connected to it. We remove infringing material promptly.</li>
            <li><strong>Privacy requests</strong> (access or deletion): describe what you used on the site and roughly when. See the <a href="{{ route('pages.privacy') }}">privacy policy</a> for what we store.</li>
            <li><strong>Bugs</strong>: the page link, your device and browser, and what happened.</li>
        </ul>
        <p>Please do not send passwords, card numbers or other sensitive information by email.</p>
    </article>
</x-layout>
