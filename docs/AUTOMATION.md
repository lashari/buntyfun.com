# BuntyFun — Content Automation (core, 16 Nov 2026)

What the automation does, where it is controlled, and what the server needs.
Everything here is controlled from **Admin → AI Studio / Automation / Calendar**; no code
edits are needed to change jobs, times, priorities or budgets.

## The flow

```
cron (every minute) → schedule:run → automation:run-due (every 5 min, locked)
        │
        ├─ generate_drafts (daily 02:00 UTC)  → picks gaps → AI writes drafts → quality checks
        │                                        → AI Studio "Waiting"  (NOTHING is published)
        │
   person in AI Studio: edit · re-check · approve now / schedule / send to review · reject · regenerate
        │
        ├─ publish_scheduled (every 5 min)     → publishes approved items whose time has come
        │                                        → sitemap.xml + feed.xml update at once (built live)
        ├─ health_report (daily 06:00 UTC)    → content counts, failed jobs, AI spend, key status
        ├─ broken_links (weekly Mon 03:00)    → every sitemap URL must return 200
        └─ content_review (weekly Mon 04:00)  → seasonal items > 11 months, items with 0 views in 90 days
```

## Safety rules built in

| Rule | Where |
| --- | --- |
| AI output is always a draft; only a person publishes | `DraftApprover` |
| Editors can only send drafts to review; admins publish/schedule | `DraftApprover`, `HandlesPublishing` |
| A draft with a failed blocking check cannot be approved | `AiDraft::hasBlockingFailure()` |
| Islamic names: meaning must be verified by a person against an **approved reference** (Settings) | `NameTemplate::extraChecks()` |
| Duplicates (≥ 50 % word-pair overlap with existing content) are blocked | `QualityChecker` |
| No links, emails, phone numbers, AI filler ("as an AI", "[name]"), keyword stuffing | `QualityChecker` |
| Urdu drafts must be in Urdu script (≥ 70 % Urdu letters) | `QualityChecker` |
| Same draft can never create two items (row lock + status check) | `DraftApprover::approve()` |
| Scheduled publish never double-publishes (conditional UPDATE) | `ScheduledPublisher` |
| A job never runs twice at once (cache lock per job) | `AutomationRunner::run()` |
| Failures retry after 10 / 20 / 40 min, then wait for the normal slot and alert | `AutomationRunner` |
| Generation stops at the monthly AI budget, and when 50 drafts are waiting | `GenerateDraftsTask` |
| Condolence messages never show ads | `Message::CATEGORIES` |
| Pages marked "Hide from Google" get `noindex` and leave the sitemap/feed | `HasContentMeta::scopeIndexable()` |
| New jobs are scheduled on first sight, never fired straight after a deploy | `AutomationRunner::runDue()` |

## What decides what gets written

`Settings → Category priority` sets a weight per type (birthday / message / urdu / name; 0 = off,
names default to 0). The daily job splits *Drafts per day* by those weights, nudged up to +50 %
toward sections people actually opened in the last 28 days (`content_views`, bots excluded).
Inside a type, each template picks the emptiest topics first (e.g. relationship × tone
combinations with the fewest wishes; message categories with the fewest items; New Year is
boosted Nov–Jan).

## Server requirements

| Need | Value |
| --- | --- |
| Cron (cPanel → Cron Jobs), every minute | `/usr/local/bin/ea-php84 /home/xsgowajv/buntyfun/release/<release>/artisan schedule:run >> /dev/null 2>&1` |
| AI key (`.env`) | `GEMINI_API_KEY=` (or `AI_PROVIDER=openai` + `OPENAI_API_KEY=`) |
| Database | run `docs/sql/2026_11_16_automation_core.mysql.sql` once in phpMyAdmin (no SSH) |
| Mail for failure alerts (optional) | `MAIL_MAILER=smtp` + SMTP settings; otherwise alerts go to `storage/logs/laravel.log` and the dashboard |

The Automation page shows a yellow banner when no job has run for 20 minutes (= cron not set).

## Environment variables used

| Variable | Purpose |
| --- | --- |
| `AI_PROVIDER` | `gemini` (default) or `openai` |
| `GEMINI_API_KEY` / `OPENAI_API_KEY` | AI key, server-side only, never shown in admin or pages |
| `AI_DAILY_FREE_LIMIT` | visitor AI Fun Studio limit (unchanged) |
| `GA4_MEASUREMENT_ID`, `ADSENSE_CLIENT_ID` | unchanged |
| `MAIL_*` | optional, for failure alert emails |

## Not built yet (needs credentials first)

- **Social auto-posting**: needs a Meta developer app (Page + Instagram publishing permissions,
  Meta app review) and a Pinterest app. Drafts already carry a social caption + image brief.
- **GA4 / Search Console weekly report inside BuntyFun admin**: needs a Google service account
  with read access to both properties. (DueClix's daily report already reads BuntyFun's GSC.)
- **Ad placement manager / AdSense**: after policy readiness; condolence exclusion is in place.

## Manual commands (if ever given SSH)

```
php artisan automation:run-due          # what cron does
php artisan automation:run health_report
```
