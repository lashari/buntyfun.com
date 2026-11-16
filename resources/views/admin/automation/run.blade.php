<x-admin-layout title="Run #{{ $run->id }}">
    <a href="{{ route('admin.automation.index') }}" class="text-sm text-slate-500 hover:underline">← Automation</a>
    <h1 class="mt-2 text-2xl font-bold">{{ $run->job_key }} · run #{{ $run->id }}</h1>
    <p class="mt-1 text-sm text-slate-600">{{ $run->status }} · {{ $run->started_at->format('m-d-Y H:i:s') }} → {{ $run->finished_at?->format('H:i:s') ?? '…' }} · by {{ $run->triggered_by }} · items {{ $run->items }}</p>
    <p class="mt-4 rounded-lg bg-white p-4 text-sm">{{ $run->message }}</p>
    @if ($run->input_tokens || $run->output_tokens)
        <p class="mt-2 text-xs text-slate-500">Tokens {{ $run->input_tokens }} in / {{ $run->output_tokens }} out · est. ${{ number_format($run->cost_usd, 5) }}</p>
    @endif
    @if ($run->details)
        <pre class="mt-4 overflow-x-auto rounded-lg bg-slate-900 p-4 text-xs text-slate-100">{{ json_encode($run->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
    @endif
</x-admin-layout>
