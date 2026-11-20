<x-admin-layout title="Automation settings">
    <a href="{{ route('admin.automation.index') }}" class="text-sm text-slate-500 hover:underline">← Automation</a>
    <h1 class="mt-2 text-2xl font-bold">Settings &amp; API</h1>

    <div class="mt-4 max-w-2xl rounded-xl border border-slate-200 bg-white p-4 text-sm">
        <h2 class="font-semibold">API status</h2>
        <ul class="mt-2 space-y-1">
            <li>AI provider: <strong>{{ config('ai.provider') }}</strong> —
                .env key {{ (config('ai.provider') === 'openai' ? config('ai.openai.key') : config('ai.gemini.key')) ? 'set ✓' : 'not set' }},
                {{ $keys->where('provider', config('ai.provider') === 'openai' ? 'openai' : 'gemini')->where('enabled', true)->count() }} admin key(s) on</li>
            <li>GA4 tag: {{ config('services.ga4.measurement_id') ?: 'not set' }}</li>
            <li>AdSense: {{ config('services.adsense.client_id') ? 'client id set' : 'not set (no ads load)' }}</li>
            <li>Mail for alerts: {{ config('mail.default') }}{{ config('mail.default') === 'log' ? ' (alerts only go to laravel.log)' : '' }}</li>
        </ul>
    </div>

    @php $canEdit = auth()->user()->isSuperAdmin(); @endphp

    <div class="mt-6 max-w-4xl rounded-xl border border-slate-200 bg-white p-4">
        <h2 class="font-semibold">AI API keys</h2>
        <p class="mt-1 text-xs text-slate-500">
            Used by the AI Fun tools and AI Studio. Requests rotate across the keys that are on (lowest priority number first, then least recently used).
            A key that hits its quota rests 10 minutes; a rejected key rests 6 hours, and the next key is used. The server <code>.env</code> key is the last fallback.
            Keys are stored encrypted and are never shown again, only the last 4 characters.
        </p>

        <table class="mt-4 w-full text-sm">
            <thead class="text-left text-xs text-slate-500">
                <tr><th class="py-1">Label</th><th>Provider</th><th>Key</th><th>Priority</th><th>Used</th><th>Last used</th><th>Status</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($keys as $k)
                    <tr class="{{ $k->enabled ? '' : 'text-slate-400' }}">
                        <td class="py-2 font-medium">{{ $k->label }}</td>
                        <td>{{ $k->provider }}{{ $k->provider === config('ai.provider') ? '' : ' (not active provider)' }}</td>
                        <td class="font-mono text-xs">{{ $k->masked() }}</td>
                        <td>{{ $k->priority }}</td>
                        <td>{{ number_format($k->use_count) }}</td>
                        <td class="text-xs">{{ $k->last_used_at?->format('m-d-Y H:i') ?? '—' }}</td>
                        <td class="text-xs">
                            @if (! $k->enabled)
                                off
                            @elseif ($k->isCoolingDown())
                                <span class="text-amber-700">resting until {{ $k->cooldown_until->format('H:i') }}</span>
                            @else
                                <span class="text-emerald-700">on</span>
                            @endif
                            @if ($k->last_error)<div class="text-red-600">{{ $k->last_error }}</div>@endif
                        </td>
                        <td class="whitespace-nowrap text-right">
                            @if ($canEdit)
                                <form method="POST" action="{{ route('admin.ai-keys.test', $k) }}" class="inline">@csrf<button class="text-navy-600 hover:underline">Test</button></form>
                                <form method="POST" action="{{ route('admin.ai-keys.toggle', $k) }}" class="ml-2 inline">@csrf<button class="text-slate-600 hover:underline">{{ $k->enabled ? 'Turn off' : 'Turn on' }}</button></form>
                                <form method="POST" action="{{ route('admin.ai-keys.destroy', $k) }}" class="ml-2 inline" onsubmit="return confirm('Delete key {{ $k->label }}? This cannot be undone.')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-3 text-slate-500">No keys added here yet{{ (config('ai.provider') === 'openai' ? config('ai.openai.key') : config('ai.gemini.key')) ? ' — the .env key is being used.' : '.' }}</td></tr>
                @endforelse
            </tbody>
        </table>

        @if ($canEdit)
            <form method="POST" action="{{ route('admin.ai-keys.store') }}" class="mt-4 flex flex-wrap items-end gap-2 border-t border-slate-100 pt-4" autocomplete="off">
                @csrf
                <label class="text-xs text-slate-500">Provider
                    <select name="provider" class="mt-1 block rounded-lg border border-slate-300 px-2 py-2 text-sm">
                        <option value="gemini">Gemini</option>
                        <option value="openai">OpenAI</option>
                    </select></label>
                <label class="text-xs text-slate-500">Label
                    <input type="text" name="label" required maxlength="60" placeholder="e.g. Gemini key 2" class="mt-1 block w-40 rounded-lg border border-slate-300 px-2 py-2 text-sm"></label>
                <label class="min-w-0 flex-1 text-xs text-slate-500">API key
                    <input type="password" name="api_key" required autocomplete="new-password" placeholder="paste key" class="mt-1 block w-full rounded-lg border border-slate-300 px-2 py-2 font-mono text-sm"></label>
                <label class="text-xs text-slate-500">Priority
                    <input type="number" name="priority" min="1" max="99" value="10" class="mt-1 block w-16 rounded-lg border border-slate-300 px-2 py-2 text-sm"></label>
                <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add key</button>
            </form>
        @else
            <p class="mt-3 text-xs text-slate-500">Only a super admin can add or change keys.</p>
        @endif
    </div>
    <form method="POST" action="{{ route('admin.automation.settings.save') }}" class="mt-6 max-w-2xl space-y-5">
        @csrf @method('PUT')
        <fieldset @disabled(! $canEdit) class="space-y-5">
            <div>
                <label class="block text-sm font-medium">Drafts per day (daily job)</label>
                <input type="number" name="drafts_per_day" min="0" max="30" value="{{ $values['drafts_per_day'] }}" class="mt-1 w-28 rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="inline-flex items-center gap-2 text-sm font-medium">
                    <input type="hidden" name="auto_publish" value="0">
                    <input type="checkbox" name="auto_publish" value="1" @checked($values['auto_publish']) class="rounded border-slate-300">
                    Auto-publish drafts that pass every check
                </label>
                <p class="text-xs text-slate-500">The daily job publishes a birthday wish, message or Urdu draft at once when all its quality checks are green. A draft with any failed check, and every name meaning, still waits in AI Studio.</p>
            </div>
            <div>
                <label class="block text-sm font-medium">Category priority (0 = off, 10 = most)</label>
                <p class="text-xs text-slate-500">The daily job splits drafts by these weights, nudged toward the sections visitors actually opened in the last 28 days.</p>
                <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach (\App\Services\Content\TemplateRegistry::all() as $key => $t)
                        <label class="text-sm">{{ $t->label() }}
                            <input type="number" name="type_weights[{{ $key }}]" min="0" max="10" value="{{ $values['type_weights'][$key] ?? 0 }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <label class="text-sm">Monthly AI budget (USD)
                    <input type="number" step="0.01" name="ai_monthly_budget_usd" value="{{ $values['ai_monthly_budget_usd'] }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
                <label class="text-sm">Price / 1M input tokens
                    <input type="number" step="0.001" name="ai_price_input_per_m" value="{{ $values['ai_price_input_per_m'] }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
                <label class="text-sm">Price / 1M output tokens
                    <input type="number" step="0.001" name="ai_price_output_per_m" value="{{ $values['ai_price_output_per_m'] }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></label>
            </div>
            <p class="-mt-3 text-xs text-slate-500">Prices are estimates for the cost column; check your provider's current price list.</p>
            <div>
                <label class="block text-sm font-medium">Approved references for Islamic name meanings (one per line)</label>
                <p class="text-xs text-slate-500">A name draft can only be approved if its source matches one of these and a person ticks "verified".</p>
                <textarea name="approved_name_references" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">{{ implode("\n", (array) $values['approved_name_references']) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium">Failure alert email</label>
                <input type="email" name="alert_email" value="{{ $values['alert_email'] }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
        </fieldset>
        @if ($canEdit)
            <button class="rounded-lg bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700">Save settings</button>
        @else
            <p class="text-sm text-slate-500">Only a super admin can change settings.</p>
        @endif
    </form>
</x-admin-layout>
