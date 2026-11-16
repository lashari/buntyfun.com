<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiDraft;
use App\Models\Setting;
use App\Services\Content\DraftApprover;
use App\Services\Content\DraftGenerator;
use App\Services\Content\TemplateRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use RuntimeException;

class AiStudioController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'generated');
        $type = $request->query('type');
        $query = AiDraft::latest();
        if ($status !== 'all') $query->where('status', $status);
        if ($type) $query->where('content_type', $type);

        return view('admin.ai-studio.index', [
            'drafts' => $query->paginate(25)->withQueryString(),
            'templates' => TemplateRegistry::all(),
            'status' => $status,
            'type' => $type,
            'counts' => AiDraft::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
            'spend' => DraftGenerator::monthSpend(),
            'budget' => (float) Setting::get('ai_monthly_budget_usd'),
            'keySet' => (bool) (config('ai.provider') === 'openai' ? config('ai.openai.key') : config('ai.gemini.key'))
                || \App\Models\AiApiKey::where('provider', config('ai.provider') === 'openai' ? 'openai' : 'gemini')->where('enabled', true)->exists(),
        ]);
    }

    public function generate(Request $request, DraftGenerator $generator)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(TemplateRegistry::all()))],
            'count' => ['required', 'integer', 'min:1', 'max:3'],
        ]);
        $budget = (float) Setting::get('ai_monthly_budget_usd');
        if ($budget > 0 && DraftGenerator::monthSpend() >= $budget) {
            return back()->withErrors('Monthly AI budget reached. Raise it in Automation → Settings.');
        }

        $made = collect(TemplateRegistry::get($data['type'])->ideas($data['count']))
            ->map(fn ($params) => $generator->generate($data['type'], $params));
        $failed = $made->where('status', 'failed');

        return redirect()->route('admin.ai-studio.index', ['type' => $data['type']])
            ->with('status', ($made->count() - $failed->count()).' draft(s) written.'.($failed->count() ? ' '.$failed->count().' failed: '.$failed->first()->error : ''));
    }

    public function show(AiDraft $draft)
    {
        return view('admin.ai-studio.show', ['draft' => $draft, 'template' => TemplateRegistry::get($draft->content_type)]);
    }

    /** Save edits to the draft and re-run every quality check. */
    public function update(Request $request, AiDraft $draft, DraftGenerator $generator)
    {
        abort_unless($draft->status === 'generated', 422, 'Only waiting drafts can be edited.');
        $template = TemplateRegistry::get($draft->content_type);
        $payload = $draft->payload ?? [];

        foreach ($template->fields() as $field => [$label, $kind]) {
            if ($kind === 'checkbox') {
                $payload[$field] = $request->boolean($field);
                continue;
            }
            $payload[$field] = $this->text($request, $field);
            if ($kind === 'select' && ! in_array($payload[$field], $template->fields()[$field][2], true)) {
                return back()->withErrors("Invalid {$label}.");
            }
        }
        if (array_key_exists('verified_by_admin', $payload)) {
            $payload['verified_by'] = $payload['verified_by_admin'] ? $request->user()->id : null;
        }
        $payload['seo_title'] = mb_substr($this->text($request, 'seo_title'), 0, 70);
        $payload['seo_description'] = mb_substr($this->text($request, 'seo_description'), 0, 170);
        $payload['social_caption'] = mb_substr($this->text($request, 'social_caption'), 0, 500);
        $payload['faq'] = collect(preg_split('/\R/', $this->text($request, 'faq')))
            ->map(fn ($l) => array_map('trim', explode('||', $l, 2)))
            ->filter(fn ($p) => count($p) === 2 && $p[0] !== '' && $p[1] !== '')
            ->map(fn ($p) => ['q' => $p[0], 'a' => $p[1]])->take(4)->values()->all();

        $generator->recheck($draft, $payload);

        return back()->with('status', 'Saved and re-checked. Score '.$draft->quality_score.'.');
    }

    public function approve(Request $request, AiDraft $draft, DraftApprover $approver)
    {
        $data = $request->validate([
            'mode' => ['required', 'in:now,schedule,review'],
            'publish_at' => ['nullable', 'required_if:mode,schedule', 'date'],
        ]);

        try {
            $approver->approve($draft, $request->user(), $data['mode'],
                $data['mode'] === 'schedule' ? Carbon::parse($data['publish_at']) : null);
        } catch (RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        $msg = ['now' => 'Published.', 'schedule' => 'Scheduled.', 'review' => 'Sent to review.'][$data['mode']];

        return redirect()->route('admin.ai-studio.index')->with('status', $msg);
    }

    public function reject(Request $request, AiDraft $draft, DraftApprover $approver)
    {
        $approver->reject($draft, $request->user(), $request->input('note'));

        return redirect()->route('admin.ai-studio.index')->with('status', 'Rejected.');
    }

    /** Write a fresh draft with the same parameters; the old one is rejected. */
    public function regenerate(Request $request, AiDraft $draft, DraftApprover $approver, DraftGenerator $generator)
    {
        $params = $draft->params ?? [];
        if ($draft->content_type === 'name') {
            $params = TemplateRegistry::get('name')->ideas(1)[0] ?? $params;
        }
        if (in_array($draft->status, ['generated', 'failed'], true)) {
            $approver->reject($draft, $request->user(), 'regenerated');
        }
        $new = $generator->generate($draft->content_type, $params);

        return redirect()->route('admin.ai-studio.show', $new)->with('status', $new->status === 'failed' ? 'Regenerate failed: '.$new->error : 'New draft written.');
    }

    /** A form text field; anything that is not a plain string counts as empty. */
    private function text(Request $request, string $key): string
    {
        $v = $request->input($key);

        return is_scalar($v) ? trim((string) $v) : '';
    }
}
