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

## Social publishing (Facebook Page + Instagram)

Admin → **Social**. Official Meta Graph API only (no browser automation).

- Meta app **BuntyFun Publisher** (ID 2532222677188710), Facebook Login for Business configuration
  `1909608247125001`, redirect URI `https://buntyfun.com/admin/social/meta/callback`. The app stays in
  Development mode: it only needs to work for its own admins (posting to our own Page), so no App Review.
- `.env`: `META_APP_SECRET=` (App settings → Basic → App secret). App ID / config ID have defaults.
- Super admin clicks **Connect Facebook & Instagram** → Facebook's own dialog → the server swaps the code
  for a long-lived user token and stores each Page token (does not expire) **encrypted**; the linked
  Instagram business account uses the same Page token. Tokens never pass through the browser or logs.
- Posts: editors save drafts, admins approve/schedule; `social_publish` job (every 5 min) posts approved
  ones. Claim `approved → publishing` makes double posting impossible. Temporary errors retry every
  15 min (3 tries); permission/token errors fail at once and flag the account.
- Instagram needs an image (upload or URL; JPG/PNG, ≥ 320 px). Facebook can be a link post.
- Every link gets `utm_source=<platform>&utm_medium=social&utm_campaign=bf_social&utm_content=post<id>`;
  clicks show in GA4 traffic acquisition. Post permalinks are stored.
- "Worth posting" lists content by real views + 5× shares in the last 30 days.

## Not built yet (needs credentials first)

- **Pinterest auto-posting**: needs a Pinterest app.
- **GA4 / Search Console weekly report inside BuntyFun admin**: needs a Google service account
  with read access to both properties. (DueClix's daily report already reads BuntyFun's GSC.)
- **Ad placement manager / AdSense**: after policy readiness; condolence exclusion is in place.

## Manual commands (if ever given SSH)

```
php artisan automation:run-due          # what cron does
php artisan automation:run health_report
```
