# BuntyFun — Architecture

## Decision: Laravel 12 (PHP 8.4) + MySQL, deployed to the same WebSouls cPanel account as eDirect360

Confirmed directly from eDirect360's own `docs/ARCHITECTURE.md`, which runs on this exact account:

- CloudLinux + LiteSpeed, PHP via EasyApache (`ea-php82` … `ea-php85`)
- MySQL databases managed in cPanel
- cron jobs
- Deploys by uploading a zip in cPanel File Manager — **no long-running Node process, no
  PostgreSQL, no Redis**. Node.js Selector (v20, confirmed enabled) is for build tooling
  (Vite) only.

A Next.js SSR server cannot run here, so BuntyFun uses the same adaptation eDirect360 already
proved out on this account:

| Need | Used | Why |
| --- | --- | --- |
| Interactive pages (quizzes, games) | Laravel Blade + Alpine.js | Server-rendered HTML, light client-side interactivity bolted on per-component — no SPA, no hydration |
| Backend structure | Laravel MVC, `app/Models` + controllers | Standard Laravel; no need for a separate API layer at Release 1 scope |
| Database | MySQL 8 / MariaDB (InnoDB) in production, SQLite locally | Matches cPanel's managed MySQL; SQLite only for fast local dev |
| Background work | Laravel queue, `database` driver, driven by cron (`schedule:run` every minute) | Durable, retryable jobs with no daemon process |
| Styling | Tailwind CSS 4, built locally via Vite | Built assets ship in the deploy zip; no Node needed on the server |
| Interactivity | Alpine.js | Lightweight — quiz steppers, game state, copy/share buttons — without a JS framework build complexity |

If traffic later outgrows shared hosting, this Laravel app moves to a VPS unchanged (swap the
queue driver to Redis, add a worker process) — same path eDirect360 already documented for itself.

## Core model (Release 1 slice)

- `categories` (department + tree) — shared taxonomy across every department
- `quizzes` → `quiz_questions` → `quiz_answers` — one reusable engine for trivia *and*
  personality/trait quizzes (`is_correct` for trivia, `score_value` for trait scoring)
- `quiz_results` — one row per completed attempt, addressable by `result_slug` for sharing
- `games` → `game_scores` — a catalog entry + optional local leaderboard per game
- `birthday_messages` — relationship × tone × language, each independently publishable
- `name_entries` — Islamic names directory; `source_ref` is a required column, never optional
- `content_views` / `share_events` — minimal, timestamp-only event logs feeding admin analytics
- `audit_logs` — every publish/unpublish/edit by an admin, enforced via `AuditLog::record()`
- `urdu_contents` — Urdu Entertainment (greetings, jokes, quotes, inspirational, love,
  friendship, festival; no poetry — copyright risk). Urdu birthday wishes reuse
  `birthday_messages` with `language='ur'` rather than a separate table.
- `wallpapers` — real uploaded files (`storage/app/public/wallpapers`), not URLs to someone
  else's hosting; `width`/`height` auto-detected from the file at upload time via `getimagesize()`.
- `ai_tools` / `ai_usages` — AI Fun Studio's catalog and per-visitor daily usage counter.

## AI Fun Studio

`app/Services/Ai/AiProviderContract` is the abstraction the spec asks for; `GeminiProvider` and
`OpenAiProvider` both implement it, but only one is *active* at a time via `AI_PROVIDER` in
`.env` (default `gemini`) — per the spec's own "start with one provider" instruction, this is an
abstraction ready for both, not two paid integrations running at once.

- **Server-side only**: the browser POSTs raw user input to our own `/ai-fun/{tool}/generate`
  endpoint; the provider call and its API key live entirely in `app/Services/Ai`, never in
  Blade/JS. No key is ever sent to the browser.
- **Rate limiting**: `throttle:6,1` (6/minute) on the route, plus a configurable daily cap
  (`AI_DAILY_FREE_LIMIT`, default 10) per tool, keyed on a hashed IP (never stored raw) —
  enforced in `AiFunController::remaining()`, not just suggested in the UI.
  A per-IP cap is coarser than per-account (it's shared by anyone behind the same IP, e.g. an
  office or a carrier NAT), accepted deliberately for now since there's no visitor account
  system yet; moving to a per-account cap is natural once one exists.
- **Error handling**: `AiGenerationException` messages are always safe to show a user; the real
  reason (missing key, HTTP failure, empty response) is logged server-side via `Log::warning`/`Log::error`
  and never reaches the response body.
- **Not yet built, documented as a gap rather than silently skipped**: real abuse/content
  moderation beyond length limits and a prompt-level tone instruction (no moderation API wired
  up), and a premium-tier bypass of the daily cap (no visitor accounts or payments exist yet to
  hang that off of).
- **Confirmed working live**: a real `GEMINI_API_KEY` was added and tested against the actual
  Gemini API through the full stack (HTTP endpoint → CSRF → rate limit → provider → real
  response → usage logged) — not just the `Http::fake()` tests in `tests/Feature/AiFunStudioTest.php`,
  which cover request shape, response parsing, and the daily-limit/not-configured paths without
  spending real API calls on every test run. `OPENAI_API_KEY` is saved too but untested from
  this sandbox — `api.openai.com` is blocked by this environment's own network policy
  (confirmed via direct curl: `CONNECT tunnel failed`), unrelated to the key or the code.
- **Model name lesson**: `GeminiProvider`'s default model is `gemini-flash-lite-latest`, a
  "latest" alias — not a pinned version. Pinned names like `gemini-1.5-flash` and
  `gemini-2.5-flash` both turned out to be already retired by the time this was tested live,
  each with a 404 naming its replacement. A pinned model name is a silent expiry date; the
  `-latest` alias tracks whatever Google currently considers current.

## Urdu / RTL

`/ur/entertainment` is a separate URL namespace per the project's own requirement. The
`<x-layout>` component takes `:dir` and `:lang` props; any page passing `dir="rtl"` gets
`dir="rtl" lang="ur"` on `<html>`, a `font-urdu` class (Noto Nastaliq Urdu, loaded only on
that page — not on every English page's load) and RTL-correct markup. Birthday wishes reuse
the same component, switching `:dir` per-row based on `$message->language`.

**Human review gate**: Urdu content carries a stricter publish bar than English content.
AI-drafted Urdu text is seeded `in_review` and must be checked by a fluent/native speaker for
naturalness before publish — self-review the way English content was reviewed does not apply
here, since fluency judgment isn't something to self-certify in a language you're less certain
of. The admin UI says this explicitly on the Urdu Content screens.

## Ads and the condolence exclusion

`categories.ads_enabled` gates ad rendering per department server-side. When the Condolence
Library department ships (Release 2/3), its route/layout **hardcodes** ads off regardless of
this flag — never relies on the database value alone. See the blueprint doc, section I, for
the full rationale.

## Roles

A single `role` enum column on `users` (`super_admin`, `admin`, `editor`, `moderator`) —
no separate roles/permissions tables. Four fixed roles with fixed behavior don't need dynamic
permission assignment; `User::canPublish()` / `canModerate()` / `isSuperAdmin()` are the checks
controllers use. Every publish/unpublish/edit calls `AuditLog::record()`.

## Hosting checklist (before go-live)

- Confirm `ea-php84` (or latest available) in MultiPHP Manager for buntyfun.com
- Create a MySQL database + user in cPanel; credentials only in the server `.env`, never in git
- Document root → `public/` (or the addon-domain folder points at it)
- cron: `* * * * * php artisan schedule:run`
- Do not touch the eDirect360 production account or its cron/cPanel settings — separate domain,
  separate database, same account only in the sense of shared hosting capacity
