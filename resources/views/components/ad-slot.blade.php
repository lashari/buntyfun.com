@props(['enabled' => true])
{{-- A manual display unit needs a real numeric ad-unit id (ADSENSE_SLOT_ID) from the AdSense
     account. Without one, nothing is rendered and placement is left to Auto ads (the
     adsbygoogle script in the layout). Never assumes approval, never invents ids.
     $enabled is false on pages that must not show ads (condolence, noindex, policy pages, and
     anything Admin → Ads turns off). The per-page unit limit and device setting come from Admin → Ads.
     Only place this away from game controls, quiz answers, copy/download buttons and navigation. --}}
@if ($enabled && config('services.adsense.client_id') && config('services.adsense.slot_id') && \App\Services\Ads\AdControls::takeUnit())
    <aside class="mx-auto my-8 max-w-3xl px-4 {{ \App\Services\Ads\AdControls::deviceClass() }}" aria-label="Advertisement">
        <p class="mb-1 text-center text-[11px] uppercase tracking-wide text-slate-400">Advertisement</p>
        <ins class="adsbygoogle" style="display:block; min-height:100px" data-ad-client="{{ config('services.adsense.client_id') }}"
             data-ad-slot="{{ config('services.adsense.slot_id') }}" data-ad-format="auto" data-full-width-responsive="true"></ins>
        <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
    </aside>
@endif
