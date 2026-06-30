# AGENTS.md — Coder Instructions (LFC Fast-Track Demo)

You are the implementation agent for the **Lusail Football Academy (LFC)** recruitment + loyalty system. A human PM (Claude/Opus) reviews every branch you produce. Follow these rules.

## What we're building
A **client demo** (not production) via the fast-track in `implementation-plan.md` §8: **Phase A** (web admin) + a **thin accounts/API slice** + **Phase D core** (mobile loyalty + VVIP). **Skip Phase B and most of C.**

## Sources of truth
- `implementation-plan.md` — full spec. Read **§4 (Phase A)**, **§6 (foundation slice)**, **§7 (Phase D)**, **§8 (demo cuts = authoritative scope)**.
- `BUILD-TASKS.md` — the backlog (T1–T10), dependency order, and the PM's review checklist. Implement only the task you're assigned; don't pull ahead.

## Stack (don't deviate)
- Laravel 11/12, PHP 8.2+, MySQL 8.
- **Filament v5** for all admin UI (Panels, Resources, Infolists, Widgets, Actions).
- RBAC: `spatie/laravel-permission` via Filament Shield.
- API: Laravel REST, `laravel/sanctum`, versioned `/api/v1`, rate-limited.
- Mobile: **Flutter** (iOS + Android), **Arabic + English with RTL** — AR is required in the demo.
- Push: Firebase Cloud Messaging.

## Demo scope rules (§8) — do NOT over-build
- Demo-grade: **seed** demo data; no real import module.
- Distribute via TestFlight/APK — no app-store submission, no store privacy-policy work.
- Relaxed hardening: basic one-scan dedupe is enough; don't build full anti-fraud / offline-sync.
- Mark accepted candidates as "players" — **no B7 joining workflow**.
- Build **AR + EN / RTL** in the Flutter app from the start.
- If a task tempts you to build Phase B or full-C features, **STOP — out of scope.**

## Non-negotiable correctness rules
- **Points = append-only ledger.** Balance is derived from transactions (earn/redeem/expire/adjust/reverse). Never store or edit a balance directly.
- **Attendance scan:** rotating signed QR shown by the parent, scanned by staff; validate signature + freshness + match is "open for scanning"; **one scan per parent per match** (DB unique constraint); credit the parent's linked player(s) on that match's team.
- **Points are per player; VVIP is a flag on the parent account.**
- **VVIP-only offers must be hidden from non-VVIP accounts.**
- **Identity documents on a private disk** (never web-served); access only via authenticated/signed routes.
- **Percentage earning rules:** no payments module yet — take the percentage of an admin-entered amount; do not assume real fee data.

## Workflow
- **One task per branch.** Branch: `task/T<id>-<slug>` (e.g. `task/T1-scaffold`).
- Small, focused commits. Don't merge to `main` — the PM reviews the branch diff first.
- **Run migrations + the test suite before committing.** Never commit failing tests.
- Add/update tests for critical logic (status transitions, ledger, scan dedupe, VVIP visibility).
- When done, write a **short summary (≤10 lines)** of what changed + how you verified it, for PM review.

## Conventions
- Follow Laravel + Filament idioms; match existing code style.
- Migrations for every schema change; seeders for demo data.
- Keep secrets in `.env`; never commit credentials.
