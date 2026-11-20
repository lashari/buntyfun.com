@php $guide = \App\Content\AiToolGuides::for($tool->slug); @endphp
<x-layout :title="$tool->title.' — Free Online AI Tool | BuntyFun'" :description="$tool->description.' Type a few words, get a result in seconds, copy it. Free, no sign-up.'"
          :meta="$tool->meta" :breadcrumbs="['AI Fun Studio' => route('ai-tools.index'), $tool->title => null]">
    <div class="mx-auto max-w-xl px-4 py-10"
         x-data="{
             input: '',
             output: null,
             error: null,
             loading: false,
             remaining: {{ $remaining }},
             async generate() {
                 if (!this.input.trim() || this.loading) return;
                 this.loading = true;
                 this.error = null;
                 this.output = null;
                 try {
                     const res = await fetch('{{ route('ai-tools.generate', $tool) }}', {
                         method: 'POST',
                         headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                         body: JSON.stringify({ input: this.input }),
                     });
                     const data = await res.json();
                     if (!res.ok) {
                         this.error = data.error || 'Something went wrong.';
                         if (data.errors?.input) this.error = data.errors.input[0];
                     } else {
                         this.output = data.output;
                         this.remaining--;
                     }
                 } catch (e) {
                     this.error = 'Something went wrong — please try again.';
                 } finally {
                     this.loading = false;
                 }
             },
         }">
        <h1 class="text-2xl font-bold">{{ $tool->title }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $tool->description }}</p>

        <form @submit.prevent="generate()" class="mt-6">
            <textarea x-model="input" rows="2" maxlength="300" placeholder="Type something to get started…"
                      class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-brand-500 focus:outline-none"></textarea>
            <div class="mt-3 flex items-center justify-between">
                <p class="text-xs text-slate-500" x-text="`${remaining} free generation${remaining === 1 ? '' : 's'} left today`"></p>
                <button type="submit" :disabled="loading || remaining <= 0"
                        class="rounded-full bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700 disabled:opacity-50">
                    <span x-show="!loading">Generate</span>
                    <span x-show="loading" x-cloak>Generating…</span>
                </button>
            </div>
        </form>

        <div x-show="error" x-cloak class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" x-text="error"></div>

        <div x-show="output" x-cloak class="mt-6 rounded-xl border border-slate-200 bg-white p-5" x-data="{ copied: false }">
            <p class="whitespace-pre-line text-lg leading-relaxed" x-text="output"></p>
            <button type="button" @click="navigator.clipboard.writeText(output); copied = true; setTimeout(() => copied = false, 2000)"
                    class="mt-4 rounded-full border border-slate-300 px-4 py-1.5 text-sm hover:bg-slate-50">
                <span x-show="!copied">Copy</span>
                <span x-show="copied" x-cloak>Copied!</span>
            </button>
        </div>

        @if ($guide)
            <section class="mt-10 prose-page">
                <h2>How it works</h2>
                <ol>
                    <li><strong>What to type:</strong> {{ $guide['input'] }}</li>
                    <li><strong>What you get:</strong> {{ $guide['output'] }}</li>
                    <li>Press <em>Copy</em> to paste the result anywhere. Press <em>Generate</em> again for a different version.</li>
                </ol>
                <h3>Ideas to try</h3>
                <ul>
                    @foreach ($guide['examples'] as $ex)
                        <li><button type="button" class="text-navy-600 underline" @click="input = @js($ex); $el.closest('[x-data]').scrollIntoView({ behavior: 'smooth' })">{{ $ex }}</button></li>
                    @endforeach
                </ul>
                <h3>Good for</h3>
                <ul>
                    @foreach ($guide['uses'] as $use)
                        <li>{{ $use }}</li>
                    @endforeach
                </ul>
                @isset($guide['note'])<p>{{ $guide['note'] }}</p>@endisset
            </section>
        @endif

        <section class="mt-8 rounded-lg bg-slate-100 p-4 text-xs leading-relaxed text-slate-600">
            <p><strong>Limits:</strong> {{ config('ai.daily_free_limit') }} free generations per tool per day; input up to 300 characters. Results are written by an AI model (Google Gemini) on the spot, are not checked by a person and can be wrong or odd. Harmful requests are blocked.</p>
            <p class="mt-2"><strong>Privacy:</strong> what you type is sent to Google to create the reply; BuntyFun does not save it. Please do not enter personal or sensitive details. See our <a href="{{ route('pages.privacy') }}" class="underline">privacy policy</a>.</p>
        </section>

        <a href="{{ route('ai-tools.index') }}" class="mt-8 block text-sm text-slate-500 hover:underline">More AI tools</a>
    </div>
</x-layout>
