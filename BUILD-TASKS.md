# Fast-Track Demo — Build Backlog

**Roles:** PM + code review = Claude (Opus, this harness). Implementation = **external opencode agent on DeepSeek**, run by the user on this same repo. (Claude's in-harness subagents are not used — they only run Claude models.)
**Spec:** `implementation-plan.md` — §4 (Phase A), §6 (foundation slice), §7 (Phase D), §8 (demo cuts — authoritative scope).
**Demo rules (§8):** seeded data, TestFlight/APK (no store review), relaxed anti-fraud/offline, **AR + EN / RTL in scope**.

**Loop:** PM expands the next task into a brief → user runs opencode on it → coder implements on a branch + commits → PM reviews diff (`/code-review`), updates status here, writes feedback → user feeds feedback to opencode → repeat. One task per branch; PM never generates the code (keeps Claude's context to diffs only).

**Git hygiene (post-T4 incident):** opencode and Claude share one working dir + HEAD. Before ANY merge/marker-commit, run `git switch main` and verify `git branch --show-current` — never assume. After each merge, assert the prior task's commit is an ancestor of `main`. Coder must branch each `task/T<n>` from current `main` (verify base).

**Status:** TODO / IN PROGRESS / IN REVIEW / DONE

## Backlog

| ID | Status | Task |
| --- | --- | --- |
| T1 | DONE | Scaffold: Laravel + Filament v5, auth, Shield roles (Admin/Coach/Mgmt), `.env`/staging, seeder skeleton |
| T2 | DONE | Phase A core (demo): Candidate resource + teams + seasons; multi-dimension status + transition guards; private-disk documents + consent; mark accepted candidate as "player" |
| T3 | DONE | Accounts/API slice: `parent_accounts`, parent↔player links, invitation; Sanctum API (auth + endpoints D needs) |
| T4 | DONE | Matches/Fixtures module (Filament) with open-for-scanning window |
| T5 | TODO | Points ledger (append-only) + earning-rules engine (fixed/percentage, scoped) |
| T6 | TODO | Attendance scan: rotating signed QR (app) + staff scanner endpoint; validate signature/freshness/open-match; one-scan dedupe; credit linked player(s) on match team |
| T7 | TODO | Redemption catalog (fees/events/merch) + redeem→voucher; VVIP flag + offers (all / VVIP-only) |
| T8 | TODO | Loyalty dashboard (Filament): issued/redeemed, attendance, fulfillment, outstanding-points liability |
| T9 | TODO | Flutter app (iOS+Android, **AR+EN/RTL**): auth, players+balances, show QR, points history, redeem, offers (may split T9a wiring / T9b screens) |
| T10 | TODO | Seed demo data + scripted end-to-end walkthrough (parent → scan → points → redeem → VVIP offer) |

## Dependencies

T1 → T2 → T3 → (T4–T8 partly parallel) → T9 → T10.

## PM review checklist

- Matches §7/§8 scope — no over-build beyond the demo cut.
- Ledger integrity: balance = Σ transactions; no direct balance edits.
- Scan: one-scan dedupe; correct player credited for the match's team.
- VVIP-only offers hidden from non-VVIP accounts.
- Private document access enforced.
- AR/RTL renders correctly.

## Carry-over follow-ups

- ~~T1 → T2: widen `User::canAccessPanel()` beyond Admin~~ — **DONE in T2** (`hasAnyRole(['Admin','Coach','Management'])`).
- ~~T1 → T2: documents on the `private` disk~~ — **DONE in T2** (private disk + authenticated streamed download).
- ~~T2 fold-ins: doc-type "Parent QID/passport", hide recruitment_stage on create, download test~~ — **DONE in T3** (9 doc types; signed+auth download with exact-bytes test).
- **T4 (2026-06-30):** recovered from a branch mishap that had frozen `main` at an old commit; `main` is now correct (T1–T4). Trivial nit left in `FixtureSeeder` (`$season` looked up but unused — uses `$team->season_id`).
- **From T3 (review fix):** `LFC_DEMO_PARENT_EMAIL/PASSWORD` were in README but not `.env.example` — **added to `.env.example` and the local `.env`**. Seeder falls back to defaults `parent.demo@lfc.test` / `password`.
- **Stack note:** running on Laravel 13 / PHP ^8.3 (latest stable — fine).
