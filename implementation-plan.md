# Lusail Football Academy — Technical Implementation Plan

Laravel + Filament admin, shared REST API, Flutter mobile app.

Delivered in controlled phases so the academy can launch fast and expand on real usage, not build everything at once.

> **Current target: a client demo via the fast-track in §8** — Phase A + a thin accounts/API slice + Phase D core, skipping B and most of C. The full phase plan below is the production roadmap.

| Phase | Scope | Effort (1 dev)* |
| --- | --- | ---: |
| 0 | Discovery — lock QFA/FIFA + document rules | 3–5 days |
| A | MVP recruitment system | 4–6 weeks |
| B | Full internal recruitment management | 4–6 weeks |
| C | Parent/player portal + **API foundation** | 4–6 weeks |
| D | Mobile app (Flutter) + loyalty & VVIP | 8–12 weeks |

\* Effort, not calendar dates. Assumes Phase 0 sign-off, client feedback within 2 business days, and no mid-phase scope change. Third-party gates (QFA/FIFA, WhatsApp BSP, app-store review) add calendar time.

---

## 1. Architecture & Stack

The admin is **Filament v5** (current stable line as of June 2026; Livewire 3 + Alpine + Tailwind). A large part of this system is structured CRUD, RBAC, dashboards, filtering, and exports — Filament ships these, so build effort goes into the recruitment-specific logic.

| Layer | Choice |
| --- | --- |
| Framework / PHP | Laravel 11/12, PHP 8.2+ |
| Admin / UI | Filament v5 (Panels, Resources, Infolists, Widgets, Actions) |
| RBAC | `spatie/laravel-permission` via Filament Shield |
| Audit log | `spatie/laravel-activitylog` |
| Exports / PDF | Filament exports + `maatwebsite/excel`; `barryvdh/laravel-dompdf` |
| DB / queue / cache | MySQL 8, Redis + Supervisor |
| File storage | Private disk (non web-served), signed/auth access only |
| Mobile API (C/D) | Laravel REST API, `laravel/sanctum`, versioned + rate-limited |
| Mobile app (D) | Flutter (iOS + Android), Arabic + English / RTL |
| Push (D) | Firebase Cloud Messaging |

**Built-in (don't custom-build):** auth, role-gated nav, CRUD, global search, filters, table exports, dashboard widgets, second panel for the parent portal.
**Custom work:** recruitment workflow, QFA/FIFA tracking, document completion logic, joining checklist, communication log, notifications, public registration page, the REST API, and the entire mobile app + loyalty engine.

---

## 2. Key Technical Decisions

These shape the data model and are the decisions worth getting right up front.

1. **Status is modeled as independent dimensions, not one flat enum.** A candidate has separate `recruitment_stage`, `document_status`, `qfa_status`, `fifa_status`, `joining_status`, each with its own allowed transitions and history. A single linear status cannot represent real states (e.g. *Accepted* + *75% documents* + *QFA submitted* + *not yet joined*) and produces invalid combinations.

2. **Minors' data is a foundational concern, not a later add-on.** Identity documents (passports, QIDs, birth certificates) live on a **private disk** from day one, with file validation, consent capture at registration, role-gated access, and a defined **retention policy** for rejected/withdrawn applicants. Retrofitting this is expensive.

3. **API-first.** Because the academy wants web *and* mobile, the backend exposes **one Sanctum REST API** built during Phase C and reused by the Flutter app in Phase D. Same accounts, one backend.

4. **Points are an append-only ledger.** Loyalty balances are **derived from a transaction log** (earn/redeem/expire/adjust/reverse), never an editable number. This is auditable and reversible.

5. **Phasing/dependencies:** B and C extend A. D depends on the **accounts + parent↔player link + API** introduced in C. See §8 for re-sequencing options.

---

## 3. Phase 0 — Discovery

The riskiest inputs come from outside the developer's control. A few days up front protect Phase A's timeline.

- Confirm the **QFA/FIFA submission process** with the registrar: documents, order, return/rejection reasons, reference formats.
- Lock the **document checklist** (mandatory vs optional, per player type).
- Confirm **roles** and who performs each action.
- Confirm **consent + retention** rules for minors' documents.
- Set realistic **WhatsApp** expectations (see §7).
- Agree **acceptance criteria / sign-off** for Phase A.

**Output:** signed-off requirements note + final field/document list + status-model confirmation.

---

## 4. Phase A — MVP Recruitment System

**Goal:** replace Google Forms / Excel / folders with a working tracker: *register → review → assess → decide → track documents + basic QFA/FIFA*.

- **Foundation:** Filament panel, auth, roles (Shield: Admin / Coach / Management), responsive admin, staging env + seeded demo data.
- **Privacy foundation (§2.2):** private disk, file type/size validation, consent capture, retention policy, HTTPS, encrypted backups.
- **Candidate resource:** all Google-Form fields + email, notes, season, consent. Add / edit / view (Infolist) / soft-delete-archive / global search.
- **Status (multi-dimension):** recruitment stage + document status + independent QFA/FIFA + joining, with code-level transition guards.
- **Assessment (basic):** note, date, recommendation (Accept/Waiting/Reject), ratings (Technical/Physical/Discipline/Potential). No scoring formulas yet.
- **Document checklist:** per-doc status (Pending/Received/Approved/Rejected), upload/replace/note — private storage.
- **QFA/FIFA (basic):** two independent fields (Not Started/Submitted/Approved/Returned) + submission date, reference, notes.
- **Dashboard + export:** count + chart widgets; Excel export of candidate lists and missing-documents.

**Exit:** deployed to production, **tested backup/restore**, admin trained, acceptance sign-off.

---

## 5. Phase B — Full Internal Recruitment Management

Upgrades the MVP into a complete internal system.

- **Governed workflow + history:** allowed transitions per dimension; full status history (value, by, at, note, optional attachment).
- **Activity log:** `spatie/laravel-activitylog` surfaced in Filament (created/updated/status/assessment/document/QFA-FIFA/team/login/export).
- **Assessment sessions:** schedule sessions, assign candidates + assessor, attendance, multi-rating evaluation, coach recommendation → admin decision.
- **Document management:** configurable types (required/optional per player type), statuses incl. Under Review / Expired, expiry date, rejection reason, **version history**, download-all (zip), **completion %** that drives the document dimension.
- **QFA/FIFA workflows:** separate, full lifecycles with history, reference numbers, attachments, approval dates. *(Exact states come from Phase 0.)*
- **Team joining:** assign age group / team / coach, joining + training dates, kit/orientation/registration status, joining checklist → final "Joined Team".
- **Communication log:** call/WhatsApp/email/meeting notes, staff, result, follow-up date.
- **Message templates:** invitation / accepted / waiting / rejection / missing-doc / QFA-FIFA update / joining. System generates; sending stays manual until C.
- **Dashboard + reports:** pipeline, distributions, readiness, joined-this-season; Excel + PDF reports.
- **Import:** CSV/Excel with column mapping, validation, dedupe, result summary — queued. *(Larger than it looks — budget explicitly.)*
- **Advanced filters** across all candidate attributes.

---

## 6. Phase C — Parent/Player Portal + API Foundation

Lets parents participate and **establishes the shared API**.

- **API foundation (built here, reused by D):** versioned REST API, **Sanctum** auth, rate limiting, same accounts/roles. Build the portal against the API so web + mobile share one backend.
- **Second Filament panel** (parent guard), fully isolated from admin.
- **Parent accounts + parent↔player linking;** invitation flow (admin invites after *accepted* or *documents requested* — avoids accounts for rejected applicants).
- **Parent features:** view player profile, simplified progress (no internal notes), required documents, **upload missing documents** (private storage, admin still approves), document status + rejection reasons, contact update.
- **Reminders:** queued + scheduled (missing/rejected docs, assessments, QFA/FIFA, joining) — email + in-app, **WhatsApp/SMS-ready**.
- **WhatsApp/SMS readiness:** templates, send-by-link, provider seam. *Live Business API is a separate paid integration (§7).*
- **Multi-season management** (create/assign/compare/archive, per-season document requirements). *(Larger than it looks.)*
- **Public registration page** (branded, mobile-friendly, validation, consent, optional duplicate check).
- **Notifications** for staff and parents.

---

## 7. Phase D — Mobile App (Flutter) + Loyalty & VVIP

Native apps for **parents, players, VVIP clients** on the Phase C API. Parents earn **points per match attendance** (scanned at the gate), redeem for **fee discounts, events/experiences, merchandise**, and VVIP clients get **exclusive offers**.

**Decisions locked:**
- Earning rules: **fixed points/match** *or* **percentage**, configurable per rule.
- Redemption: one catalog across **fees + events + merch**.
- **No in-app payments yet** — redemptions issue vouchers/credits fulfilled offline; API has a payments seam for later.
- Scan: parent shows a **rotating signed QR**; **staff scan it**; server enforces **one scan per parent per match**.
- VVIP: **manual flag** by admin; offers target it.
- Points: **per player**; VVIP is a **parent-account** attribute.
- New **Matches/Fixtures** module; scans link to a match.

**Assumptions (confirmed):** iOS + Android; players view-only (parent redeems on their behalf); a scan credits the parent's linked player(s) **on that match's team**.

**Caveat:** a *percentage* rule needs a monetary base. Until the deferred fees/payments module exists, percentage rules run against an admin-entered amount — full percentage-of-fees waits for the payments phase.

**Modules:**
- **API** — auth, profile/progress, documents, points balance + ledger, scan, catalog + redeem, offers, notifications.
- **Matches** (Filament) — fixtures with an "open for scanning" window.
- **Scanning** — rotating signed QR + staff scanner (role-gated): validates signature, freshness, open match, dedupe; credits per active rule; **offline queue + sync**.
- **Earning-rules engine** (Filament) — fixed/percentage, scoped, dated.
- **Points ledger** — append-only per player; optional expiry; gated, audited manual adjustments.
- **Redemption catalog** (Filament + app) — fee/event/merch items with cost, validity, stock; redeem → voucher/booking → offline fulfillment queue.
- **VVIP & offers** (Filament) — manual flag; offers targeting all or VVIP-only; push on publish.
- **Notifications** — Firebase + in-app.
- **Flutter app** — parent (players, balances, **show QR**, redeem, offers, progress + docs), player (view-only), staff (scanner). AR/EN/RTL.
- **Loyalty dashboard** (Filament) — issued/redeemed, attendance, top earners, VVIP activity, fulfillment, **outstanding-points liability**.

---

## 8. Demo Fast-Track (selected path) — A → Foundation slice → D

**Target: a working demo for the client** — web admin (A) + a mobile loyalty experience (D) — **skipping B and most of C**. D needs only a thin slice of C, so we build that slice and jump straight to D.

**Build order:**
1. **Phase A** (demo-grade — seeded data is fine).
2. **Foundation slice** (carved from C, ~1 week): parent accounts, parent↔player linking, and the **Sanctum API** with just the endpoints D consumes.
3. **Phase D core:** matches, rotating-QR scan, points ledger, earning rules, redemption catalog, VVIP + offers, Flutter app, loyalty dashboard.

**Cut for the demo (deferred to production):**
- Seed demo data (players, parents, teams, matches) — no import module, no real recruitment workflow.
- Distribute via **TestFlight (iOS) / direct APK (Android)** — **skip app-store review** and the mandatory store privacy policy.
- Relax hardening: basic one-scan dedupe is enough; full anti-fraud + offline sync trimmed.
- Mark accepted candidates as "players" — **skip B7 joining workflow**.
- Skip all of **Phase B** and the rest of **C** (document upload, reminders, multi-season, public form).

**In demo scope:** the Flutter app ships **bilingual Arabic + English with RTL** — Arabic is required for the demo, so app strings, layout mirroring, and the admin-facing demo screens are built AR/EN from the start (not deferred).

**Demo effort estimate:** Phase A (compressible) + foundation slice (~1 week) + **Phase D core ~5–7 weeks** (vs 8–12 full; store submission, full anti-fraud/offline hardening, and catalog polish are deferred, **but AR/EN + RTL is included**).

> Nothing here is throwaway: the API, accounts, ledger, loyalty engine, and the AR/EN app all carry forward to production. Production then adds B, the rest of C, store submission + privacy policy, and full hardening/offline sync.

---

## 9. Data Model (tables by phase)

**Phase A:** users; roles/permissions; candidates *(+ status-dimension columns)*; candidate_status_histories; assessments; document_types; candidate_documents; consents; teams; seasons.

**Phase B:** assessment_sessions; assessment_session_candidates; activity_log; communication_logs; qfa_submissions; fifa_submissions; document_versions; joining_checklists(+items); report_exports; import_batches(+rows).

**Phase C:** parent_accounts; parent_player_links; parent_notifications; document_upload_requests; reminder_rules; reminder_logs; message_templates; season_settings; public_registration_settings.

**Phase D:** personal_access_tokens; device_tokens; matches; attendance_scans *(unique per parent+match)*; point_rules; point_transactions *(ledger)*; redemption_items; redemptions; offers. *(VVIP = flag on parent_accounts.)*

---

## 10. Testing Strategy

Each phase: **staging environment + seeded data**, automated tests for critical logic, manual UAT against Phase 0 criteria, and a **sign-off gate** before production deploy.

Critical paths by phase:
- **A:** role boundaries; valid/invalid status transitions; **unauthorized document access blocked**; dashboard counts; export; consent capture.
- **B:** status history; activity log; completion %; QFA/FIFA workflows; joining checklist; import (imported/skipped/failed); filters.
- **C:** parent isolation (**cannot see other candidates**); upload; progress page leaks no internal notes; reminders; public form.
- **D:** API auth/rate limits + role isolation; **QR signature + freshness + one-scan dedupe**; correct player credited; **ledger integrity (balance = Σ transactions)**; redemption deduct + stock limits; **VVIP-only offers hidden from non-VVIP**; offline scan sync; AR/RTL; store + privacy-policy compliance.

---

## 11. Deployment & Infrastructure

VPS/dedicated server: Ubuntu, Nginx, PHP 8.2+, MySQL 8, **Redis + Supervisor** (queues), scheduler (reminders), HTTPS, **daily encrypted backups + tested restore**, separate **staging**.

Per-deploy: build assets + `filament:optimize`, configure `.env` (private disk, queue, mail), migrate + seed, set storage permissions (private not web-served), start queue workers + scheduler, verify login/role boundaries + **private document access**, verify restore, acceptance sign-off.

**Phase D additions:** Sanctum + rate limiting; Firebase project/credentials; Apple Developer + Google Play accounts (client-owned), signing keys, AR/EN store listings, **privacy policy (mandatory — minors' data)**, submission + review; app version/forced-update strategy.

**Handover per phase:** admin account, Git repo, schema, deployment notes, user guide, training, known limitations.

---

## 12. Risks & Dependencies

| Risk / dependency | Mitigation |
| --- | --- |
| QFA/FIFA rules are external + unconfirmed | Lock in **Phase 0** before committing A's timeline |
| Percentage earning rules need a money base | Use admin-entered base until payments phase; flag the gap |
| WhatsApp Business API (BSP approval, templates, per-msg fees) | Phase C is *integration-ready* only; **live sending quoted/contracted separately** |
| Import + multi-season are bigger than they look | Budget explicitly; run import on the queue |
| App-store review + minors' privacy policy | Plan calendar buffer; privacy policy is mandatory for submission |
| Minors' identity documents | Private storage + consent + retention enforced from Phase A |

> Pricing/commercials are tracked in a separate proposal document, not here.
