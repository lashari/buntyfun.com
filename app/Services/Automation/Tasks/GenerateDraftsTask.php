<?php

namespace App\Services\Automation\Tasks;

use App\Models\AiDraft;
use App\Models\AutomationRun;
use App\Models\ContentView;
use App\Models\Setting;
use App\Models\User;
use App\Services\Automation\AutomationTask;
use App\Services\Content\DraftApprover;
use App\Services\Content\DraftGenerator;
use App\Services\Content\TemplateRegistry;
use RuntimeException;

/**
 * Daily: decide what to write (gaps per type, weighted by admin priorities and by
 * which sections visitors actually used in the last 28 days), then write drafts.
 * Drafts only, unless the super admin switched on "Auto-publish" in Settings:
 * then a draft whose every quality check passed is published at once (never
 * name meanings). Anything with a failed check still waits in AI Studio.
 */
class GenerateDraftsTask implements AutomationTask
{
    // content_views.content_type values per template type
    private const VIEW_TYPES = ['birthday' => 'birthday_message', 'message' => 'message', 'urdu' => 'urdu_content', 'name' => 'name_entry'];

    /** Types that may be published without a person (names need a verified reference). */
    public const AUTO_TYPES = ['birthday', 'message', 'urdu'];

    public function __construct(private readonly DraftGenerator $generator, private readonly DraftApprover $approver) {}

    public function handle(AutomationRun $run): ?string
    {
        $budget = (float) Setting::get('ai_monthly_budget_usd');
        $spent = DraftGenerator::monthSpend();
        if ($budget > 0 && $spent >= $budget) {
            $run->message = sprintf('Monthly AI budget reached ($%.2f of $%.2f).', $spent, $budget);

            return null;
        }

        // Don't pile up drafts nobody reviews.
        $waiting = AiDraft::where('status', 'generated')->count();
        if ($waiting >= 50) {
            $run->message = "{$waiting} drafts already waiting for review; not writing more.";

            return null;
        }

        $plan = $this->plan((int) Setting::get('drafts_per_day'));
        $run->details = ['plan' => $plan];
        $ok = $failed = $published = 0;
        $errors = [];
        $publisher = Setting::get('auto_publish')
            ? User::whereIn('role', ['super_admin', 'admin'])->orderByRaw("role = 'super_admin' desc")->orderBy('id')->first()
            : null;

        foreach ($plan as $type => $n) {
            foreach (TemplateRegistry::get($type)->ideas($n) as $params) {
                $draft = $this->generator->generate($type, $params, $run);
                $run->input_tokens += (int) $draft->input_tokens;
                $run->output_tokens += (int) $draft->output_tokens;
                $run->cost_usd += (float) $draft->cost_usd;
                if ($draft->status === 'failed') {
                    $failed++;
                    $errors[] = $draft->error;
                } else {
                    $ok++;
                    if ($publisher && $this->autoPublish($draft, $publisher)) {
                        $published++;
                    }
                }
            }
        }

        $run->items = $ok;
        $run->details = ['plan' => $plan, 'failed' => $failed, 'published' => $published, 'errors' => array_values(array_unique($errors))];

        if ($ok === 0 && $failed > 0) {
            throw new RuntimeException('All drafts failed: '.implode(' | ', array_unique($errors)));
        }

        $waitingNow = $ok - $published;

        return "Wrote {$ok} draft(s)".($failed ? ", {$failed} failed" : '')
            .($publisher ? ", auto-published {$published}" : '')
            .($waitingNow > 0 ? " — {$waitingNow} waiting in AI Studio for review." : '.');
    }

    /** Publish now when every check passed; false (draft keeps waiting) otherwise. */
    private function autoPublish(AiDraft $draft, User $publisher): bool
    {
        if (! in_array($draft->content_type, self::AUTO_TYPES, true)) {
            return false;
        }
        if (collect($draft->checks ?? [])->contains(fn ($c) => ! ($c['ok'] ?? false))) {
            return false;
        }
        try {
            $this->approver->approve($draft, $publisher, 'now');
            $draft->refresh()->update(['review_note' => 'Auto-published by the daily job: all quality checks passed.']);

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /** @return array<string,int> type => how many drafts today */
    public function plan(int $total): array
    {
        $weights = (array) Setting::get('type_weights');
        $views = ContentView::where('created_at', '>=', now()->subDays(28))
            ->selectRaw('content_type, count(*) as c')->groupBy('content_type')->pluck('c', 'content_type');
        $allViews = max(1, $views->sum());

        $score = [];
        foreach (self::VIEW_TYPES as $type => $viewType) {
            $w = (float) ($weights[$type] ?? 0);
            if ($w <= 0) continue; // 0 = switched off by the admin
            // Admin weight, nudged up to +50% by the share of real views the section got.
            $score[$type] = $w * (1 + 0.5 * (($views[$viewType] ?? 0) / $allViews));
        }
        if (! $score || $total <= 0) {
            return [];
        }

        $sum = array_sum($score);
        $plan = array_map(fn ($s) => (int) floor($total * $s / $sum), $score);
        arsort($score);
        foreach (array_keys($score) as $type) { // hand out the remainder to the top types
            if (array_sum($plan) >= $total) break;
            $plan[$type]++;
        }

        return array_filter($plan);
    }
}
