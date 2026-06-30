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
| T5 | DONE | Points ledger (append-only) + earning-rules engine (fixed/percentage, scoped) |
| T6 | DONE | Attendance scan: rotating signed QR (app) + staff scanner endpoint; validate signature/freshness/open-match; one-scan dedupe; credit linked player(s) on match team |
| T7 | DONE | Redemption catalog (fees/events/merch) + redeem→voucher; VVIP flag + offers (all / VVIP-only) |
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
- **T5 (2026-06-30) review:** APPROVED & merged (50 tests green; `migrate:fresh --seed` clean). Ledger append-only (model `updating`/`deleting` throw), balance = Σ txns (no stored column), rules fixed/percentage scoped+dated with deterministic resolution, percentage off admin-entered `base_amount`, Adjust-points action gated Admin/Management + audited.
  - **Defect caught:** committed branch tip imported non-existent `Filament\Tables\Actions\{Edit,Delete}Action` (would fatal the Point Rules page); a fix was sitting **uncommitted** in the working tree — committed it as `73969b1` during review. *Process feedback for opencode: commit ALL working-tree changes before handing off; the broken tip passed tests because no test renders the Filament table.*
  - **Open follow-ups (non-blocking):** Adjust action authorized via `->visible(hasRole)` only (consider `->authorize()`/policy); append-only guard is Eloquent-event level (raw query-builder `update()`/`delete()` would bypass — DB-level hardening deferred); `scopeActiveOn()` only filters `is_active` (date window lives in `resolveRule`) — naming nit. Manually click-through the Point Rules page during T10/verify to confirm render.
- **T6 (2026-06-30) review:** APPROVED & merged (73 tests green; `migrate:fresh --seed` clean). `attendance_scans` with `unique(parent_account_id, fixture_id)` dedupe; `ScanTokenService` HMAC-SHA256 token (`hash_equals` + TTL/skew freshness); `POST /staff/login` (role-gated), `GET /scan-token` (parent-only), `POST /scan` (staff-only, role-gated) — both token-type directions enforced via `instanceof`; credits only linked players on the fixture's team via `PointsEngine::credit()` (scan = morph source); attendance still recorded with 0 pts when no rule.
  - **Defect process (repeat of T5):** entire T6 arrived **uncommitted** (3 modified + 7 untracked) on a branch tip still at the T5 merge — preserved as WIP checkpoint `5b03e40` before reviewing. Coder also mis-reported "4 pre-existing test failures" — actually 0 on a clean `RefreshDatabase` run (dirty local test DB). *Reminder to opencode: commit before handoff; run tests on a fresh DB.*
  - **Open follow-ups for opencode (fold into start of T7; non-blocking for demo):** (1) **wrap scan-create + credit loop in `DB::transaction()`** — currently the scan commits before crediting, so a mid-loop `credit()` failure leaves the parent permanently under-credited + dedupe-locked (brief asked for this); (2) dedupe race-catch matches a MySQL-specific index name in the exception message → use driver-agnostic `catch (Illuminate\Database\UniqueConstraintViolationException $e)`; (3) qualifying-players query should defensively `->where('is_player', true)` and qualify `candidates.team_id`. — **ALL 3 DONE in T7 commit `7e646e4`.**
- **T7 (2026-06-30) review:** APPROVED & merged (98 tests green; `migrate:fresh --seed` clean; handoff properly committed in 5 commits this time). Redemption catalog (`redemption_items` fee/event/merch + `redemptions` w/ unique voucher) — `RedemptionService::redeem()` in `DB::transaction`+`lockForUpdate`, balance/stock/validity guards, typed exceptions → 403/422; redeem deducts via new `PointsEngine::redeem()` (`-abs(points)`, append-only, scan/redemption morph source). VVIP = Admin-gated `is_vvip` flag on `parent_accounts`, exposed in `/me`; `offers` + `Offer::visibleTo()` filters **server-side** so non-VVIP never receive `vvip` (tested through the real `/api/v1/offers` endpoint). T6 fixes (1)(2)(3) folded in.
  - **Open follow-ups (non-blocking for demo):** (a) residual same-player double-spend window — balance check is in the txn but the lock is on the *item* row; concurrent redemptions of *different* items for the *same* player don't serialize on balance → add `Candidate::lockForUpdate()->find($player->id)` at the top of the txn; (b) `RedemptionController::items()` lacks the `instanceof ParentAccount` guard the other parent endpoints have (harmless — public catalog).
