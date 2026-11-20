@if ($lines = $quiz->sourceLines())
    <section class="mt-10 rounded-xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-600">
        <h2 class="font-semibold text-slate-800">Sources</h2>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($lines as $line)
                <li>{!! preg_replace('~https?://[^\s<]+~', '<a href="$0" class="underline break-all" rel="noopener nofollow" target="_blank">$0</a>', e($line)) !!}</li>
            @endforeach
        </ul>
        <p class="mt-3">Spotted a mistake? <a href="{{ route('pages.contact') }}" class="underline">Tell us</a> and we will check and correct it.</p>
    </section>
@endif
