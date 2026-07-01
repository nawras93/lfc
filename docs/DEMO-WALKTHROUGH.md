# LFC Demo Walkthrough

## Setup

```bash
php artisan migrate:fresh --seed
php artisan serve --host=0.0.0.0 --port=8000
```

Run the Flutter app from `mobile/`.

- Android emulator: no extra flag needed. The app defaults to `http://10.0.2.2:8000/api/v1`.
- iOS simulator: no extra flag needed. The app defaults to `http://localhost:8000/api/v1`.
- Physical Android device on the same Wi-Fi:

```bash
flutter run --dart-define=API_BASE_URL=http://<mac-lan-ip>:8000/api/v1
```

The backend server must stay on `--host=0.0.0.0` for physical-device testing.

## Credentials

- Admin / staff scanner: `admin@lfc.test` / `password`
- Parent demo: `parent.demo@lfc.test` / `password`
- VVIP client demo: `vvip.demo@lfc.test` / `password`

## Seeded Numbers

After `migrate:fresh --seed`, the demo starts with:

- Omar Demo player balance: `150`
- VVIP client account balance: `500`
- Loyalty dashboard issued points: `720`
- Loyalty dashboard redeemed points: `70`
- Loyalty dashboard outstanding liability: `650`
- Pending fulfillments: `1`

Seeded catalog items used in the script:

- `Match Day VIP Pass` costs `150`
- `Registration Fee Waiver` costs `200`

## Act 1 — Admin Web

1. Open Filament and sign in with `admin@lfc.test`.
2. Open the loyalty dashboard.
3. Confirm:
   - Issued points = `720`
   - Redeemed points = `70`
   - Outstanding liability = `650`
   - Pending fulfillments widget has `1` issued redemption row
4. Open Point Rules and confirm:
   - `Match attendance — 10 pts`
   - `Bonus attendance — 5% of fee`
5. Open Redemption Items and confirm `Match Day VIP Pass` (`150`) and `Registration Fee Waiver` (`200`) are active.
6. Open Offers and confirm both:
   - `Early Bird Registration Discount`
   - `VVIP Lounge Access — Al Thumama Match`
7. Open Parent Accounts and confirm:
   - `Amina Demo` is a parent account
   - `Sheikha Demo` is a `vvip_client`
8. Open Redemptions and confirm seeded rows include one `issued` and one `fulfilled`.

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

If you refresh the admin dashboard after the full script:

- Issued points = `730`
- Redeemed points = `420`
- Outstanding liability = `310`

## Reset

```bash
php artisan migrate:fresh --seed
```
