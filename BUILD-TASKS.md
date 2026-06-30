# Fast-Track Demo — Build Backlog

**Roles:** PM + code review = Claude (Opus, this harness). Implementation = **external opencode agent on DeepSeek**, run by the user on this same repo. (Claude's in-harness subagents are not used — they only run Claude models.)
**Spec:** `implementation-plan.md` — §4 (Phase A), §6 (foundation slice), §7 (Phase D), §8 (demo cuts — authoritative scope).
**Demo rules (§8):** seeded data, TestFlight/APK (no store review), relaxed anti-fraud/offline, **AR + EN / RTL in scope**.

**Loop:** PM expands the next task into a brief → user runs opencode on it → coder implements on a branch + commits → PM reviews diff (`/code-review`), updates status here, writes feedback → user feeds feedback to opencode → repeat. One task per branch; PM never generates the code (keeps Claude's context to diffs only).

**Status:** TODO / IN PROGRESS / IN REVIEW / DONE

## Backlog

| ID | Status | Task |
| --- | --- | --- |
| T1 | TODO | Scaffold: Laravel + Filament v5, auth, Shield roles (Admin/Coach/Mgmt), `.env`/staging, seeder skeleton |
| T2 | TODO | Phase A core (demo): Candidate resource + teams + seasons; multi-dimension status + transition guards; private-disk documents + consent; mark accepted candidate as "player" |
| T3 | TODO | Accounts/API slice: `parent_accounts`, parent↔player links, invitation; Sanctum API (auth + endpoints D needs) |
| T4 | TODO | Matches/Fixtures module (Filament) with open-for-scanning window |
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
