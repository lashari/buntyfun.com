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
