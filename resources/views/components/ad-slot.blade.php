@props(['enabled' => true])
{{-- Reserved space from day one so turning ads on later doesn't shift layout.
     Renders nothing but an empty, correctly-sized container until ADSENSE_CLIENT_ID
     is set — never assumes AdSense approval. $enabled is false on pages (e.g. the
     future Condolence Library) that must never show ads, full stop. --}}
@if ($enabled && config('services.adsense.client_id'))
    <div class="mx-auto my-4 max-w-6xl px-4">
        <ins class="adsbygoogle" style="display:block" data-ad-client="{{ config('services.adsense.client_id') }}"
             data-ad-slot="auto" data-ad-format="auto" data-full-width-responsive="true"></ins>
    </div>
@elseif ($enabled)
    <div class="mx-auto my-4 max-w-6xl px-4" aria-hidden="true"></div>
@endif
