# LFC Demo Walkthrough

## Setup

```bash
php artisan migrate:fresh --seed
php artisan serve --host=0.0.0.0 --port=8000
```

Run the Flutter app from `mobile/apps/demo_app_one`.

- Android emulator: no extra flag needed. The app defaults to `http://10.0.2.2:8000/api/v1`.
- iOS simulator: no extra flag needed. The app defaults to `http://localhost:8000/api/v1`.
- Physical Android device on the same Wi-Fi:

```bash
flutter run --dart-define=API_BASE_URL=http://<mac-lan-ip>:8000/api/v1
```

The backend server must stay on `--host=0.0.0.0` for physical-device testing.

## Credentials

- Admin / staff scanner: `admin@lfc.test` / `password`
- Parent demo (one child): `parent.demo@lfc.test` / `password`
- Parent demo (two children): `parent2.demo@lfc.test` / `password`
- VVIP client demo: `vvip.demo@lfc.test` / `password`

## Seeded Numbers

After `migrate:fresh --seed`, the demo starts with:

- Omar Demo player balance: `150`
- VVIP client account balance: `500`
- Loyalty dashboard issued points: `1140`
- Loyalty dashboard redeemed points: `170`
- Loyalty dashboard outstanding liability: `970`
- Pending fulfillments: `2`

Seeded catalog items used in the script:

- `Match Day VIP Pass` costs `150`
- `Registration Fee Waiver` costs `200`

## Extra seeded scenarios (beyond the main script)

These give the admin and mobile views more realistic depth:

- **A parent with two children** — `Fatima Al-Kuwari` (`parent2.demo@lfc.test`):
  - `Yousef Al-Kuwari` — `LFC U14`, balance `220`, one **issued** voucher (Training Kit Bundle) awaiting fulfillment.
  - `Hassan Al-Kuwari` — `LFC U12`, balance `100`, one **fulfilled** voucher (Water Bottle).
  - Signing into the mobile app as this parent shows a two-player family and a combined total of `320`.
- **A recruitment-pipeline candidate** — `Tariq Al-Ansari` (not yet a player): assessment scheduled, documents in progress, QFA submitted. Appears on the admin Candidates board, not in any parent's mobile app.

## Act 1 — Admin Web

1. Open Filament at `/admin-app-one` and sign in with `admin@lfc.test`.
2. Open the loyalty dashboard.
3. Confirm:
   - Issued points = `1140`
   - Redeemed points = `170`
   - Outstanding liability = `970`
   - Pending fulfillments widget has `2` issued redemption rows (Omar's and Yousef's)
4. Open Point Rules and confirm:
   - `Match attendance — 10 pts`
   - `Bonus attendance — 5% of fee`
5. Open Redemption Items and confirm `Match Day VIP Pass` (`150`) and `Registration Fee Waiver` (`200`) are active.
6. Open Offers and confirm both:
   - `Early Bird Registration Discount`
   - `VVIP Lounge Access — Al Thumama Match`
7. Open Parent Accounts and confirm:
   - `Amina Demo` is a parent account (one child)
   - `Fatima Al-Kuwari` is a parent account (two children)
   - `Sheikha Demo` is a `vvip_client`
8. Open Redemptions and confirm seeded rows include `issued` and `fulfilled` vouchers.
9. **Fulfill a voucher:** on the dashboard's Pending fulfillments widget (or the
   Redemptions list), click **Mark fulfilled** on a row and confirm. The row
   leaves the pending list and its status becomes `fulfilled` with a
   `fulfilled_at` timestamp. Pending fulfillments drops from `2` to `1`.
   (Fulfillment is a handover record only — it does not change points, so the
   issued/redeemed/liability totals stay the same.)

## Act 2 — Parent Mobile

1. Sign in as `parent.demo@lfc.test`.
2. Players tab:
   - Omar Demo is listed on `LFC U12`
   - Omar balance shows `150`
3. QR tab:
   - A live rotating QR appears
4. Offers tab:
   - `Early Bird Registration Discount` is visible
   - the VVIP lounge offer is not visible
5. Leave the parent app signed in. The balance change happens after Act 3.

## Act 3 — Staff Scanner Mobile

1. Open staff scanner login from the app and sign in with `admin@lfc.test`.
2. Select fixture `LFC U12 vs Al Sadd SC`.
3. Scan the parent QR from Act 2, or paste the token manually.
4. Expected result:
   - Omar Demo credited `+10`
   - Total points shown = `10`
5. Return to the parent session.

## Act 2 Continued — Parent After Scan

1. Go back to the Players tab.
2. Omar balance now shows `160`.
3. Open Omar points history.
4. Confirm a new attendance credit entry for `+10`.
5. Go to Rewards and redeem `Match Day VIP Pass`.
6. Expected result:
   - Voucher dialog appears
   - Omar balance becomes `10`
   - Rewards history includes the new issued voucher

## Act 4 — VVIP Client Mobile

1. Sign in as `vvip.demo@lfc.test`.
2. Header/account chip shows `500`.
3. Offers tab shows the VVIP-only lounge offer.
4. Rewards tab:
   - no player picker is shown
   - redeem `Registration Fee Waiver`
5. Expected result:
   - Voucher dialog appears
   - header/account balance updates from `500` to `300`

## Optional Admin Recheck

If you refresh the admin dashboard after the full script (Omar scan `+10`, Omar
redeems `150`, VVIP redeems `200`):

- Issued points = `1150`
- Redeemed points = `520`
- Outstanding liability = `630`

## Notes

- **No linked player on match's team:** If a scanned parent has no linked player on the fixture's team, the scan is **rejected with 422** (`"No linked player on this match's team."`) and no scan record is created. This applies e.g. to VVIP client accounts (no player links) or parents whose children play for a different team.

## Reset

```bash
php artisan migrate:fresh --seed
```

## App Two — Lusail SC supporter app

### Setup

Use the same backend setup:

```bash
php artisan migrate:fresh --seed
php artisan serve --host=0.0.0.0 --port=8000
```

Run the Flutter app from `mobile/apps/demo_app_two`. Use the same base-URL rules as app one; see `docs/MOBILE-DEMO-APPS.md` for emulator and device notes.

### Credentials

- Member demo: `member.demo@lfc.test` / `password`
- VVIP member demo: `vvip.member.demo@lfc.test` / `password`

### Seeded app-two numbers

- Member wallet discount: `2.5%` from `5` attended matches (`+0.5%` each)
- VVIP tier: `Platinum`
- VVIP member number: `LSC-000123`
- VVIP valid until: `2027-06-30`
- Published news posts: `4`
- Results: `5`
- Upcoming fixtures: `3` (including `1` open for scanning)
- Standings rows: `8`
- Published offers: `3` (`2` All, `1` VVIP)

### Act 1 — Guest browse

1. Launch `demo_app_two` without signing in.
2. Home tab:
   - browse the club news feed
   - open a news article detail view
3. Matches tab:
   - Fixtures shows the `3` upcoming matches
   - Results shows the `5` played matches
   - Table shows `8` clubs with Lusail highlighted
4. Confirm EN/AR switching and RTL rendering both work before login.

### Act 2 — Register a new fan

1. Open the Membership tab as a guest.
2. Create a brand-new supporter account in-app.
3. Expected result:
   - the user lands in the discount wallet
   - wallet starts at `0%`
   - no VVIP card is shown

### Act 3 — Member wallet

1. Sign in as `member.demo@lfc.test`.
2. Confirm the wallet shows:
   - `2.5%` redeemable toward academy registration
   - `10%` maximum cap
   - a live rotating QR
   - `5` history rows at `+0.5%`
3. Optional live demo:
   - have staff scan this member at the open app-two fixture
   - the wallet increases by another `+0.5%`

### Act 4 — VVIP card

1. Sign in as `vvip.member.demo@lfc.test`.
2. Confirm the app shows:
   - digital `Platinum` membership card
   - member number `LSC-000123`
   - valid-until date `2027-06-30`
   - identity QR on the card
   - Platinum benefits list
   - app-two VVIP and All offers
3. Repeat the same check in Arabic to confirm RTL layout and localized content.

### Note

The on-device run for `demo_app_two` is the user's verification step after the seeded backend data is in place.
