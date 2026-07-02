# T16-fix — Restore demo_app_one theme parity (3 colours regressed)

**Branch:** continue on `task/T16-mobile-monorepo-core` (same branch as the T16 restructure). One small follow-up commit.

**Context:** The T16 monorepo refactor is good and stays. **One regression:** `BrandPalette` collapsed the Lusail navy *scale* into 4 tokens and dropped `navy600` (mid) and `navy500` (the dark-mode primary shade). Three colours the theme actually used got substituted with the nearest surviving token, so **demo_app_one no longer renders identically to pre-refactor `main`** — which the brief required. Root cause is purely the palette model missing two shades; `LfcColors.navy600`/`navy500` still exist as consts, they're just no longer referenced.

**The 3 regressed colours (Lusail / demo_app_one):**

| Where | Was | Now (wrong) | Impact |
|---|---|---|---|
| Dark `ColorScheme.primary` | `navy500` `#2E659B` | `navy700` `#113F71` | **worst** — dark-mode primary (buttons, app bar, FAB, selected states) too dark / low-contrast |
| Light `ColorScheme.tertiary` | `navy600` `#1C5288` | `navy800` `#0B3059` | minor |
| Light hero gradient end stop | `navy600` `#1C5288` | `navy800` `#0B3059` | matchday hero card gradient too dark |

---

## Fix — add the two lost shades back to `BrandPalette`, restore the 3 references

**File:** `mobile/packages/lfc_core/lib/src/branding/brand.dart` — add two fields to `BrandPalette` and to the `lusail` preset:

```dart
// in the const constructor param list:
required this.primaryMid,       // navy600 — light tertiary + light hero end
required this.primaryOnDark,    // navy500 — dark-mode ColorScheme.primary

// in `static const lusail = BrandPalette(...)`:
primaryMid: Color(0xFF1C5288),
primaryOnDark: Color(0xFF2E659B),

// add the two field declarations:
final Color primaryMid;
final Color primaryOnDark;
```

**File:** `mobile/packages/lfc_core/lib/src/theme/app_theme.dart` — restore the three references:

```dart
// _darkScheme(BrandPalette brand):
-  primary: brand.primary,
+  primary: brand.primaryOnDark,

// _lightScheme(BrandPalette brand):
-  tertiary: brand.primaryStrong,
+  tertiary: brand.primaryMid,

// LfcPalette.light(BrandPalette brand) — heroGradient:
-  heroGradient: [brand.heroEnd, brand.primaryStrong],
+  heroGradient: [brand.heroEnd, brand.primaryMid],
```

Leave everything else in `app_theme.dart` untouched (dark hero gradient, all other `brand.*` mappings, the `navy*` consts, `_buildTheme`, fonts). Do **not** change any other palette mapping — the other ~10 substitutions were verified correct.

**Because `primaryMid`/`primaryOnDark` become required constructor params:** update **every** place a `BrandPalette` is constructed so it still compiles. `BrandPalette.lusail` is the one I know of; **check `demo_app_two`/`demo_app_three` `brand.dart`** — if either builds an inline `BrandPalette` (rather than referencing `BrandPalette.lusail`), give it the two new values too (any sensible placeholder is fine for those two).

## Acceptance
- `flutter analyze` clean + `flutter test` green in `packages/lfc_core` and all three `apps/*` (constructor change must not break the widget/brand tests).
- **Parity check — the point of this task:** run `demo_app_one` and confirm against pre-refactor `main`:
  - **Dark mode:** primary-coloured surfaces (app bar / buttons / FAB / selected states) are the lighter `#2E659B` navy again, not the darker `#113F71`.
  - **Light mode:** the matchday hero card gradient ends on the mid `#1C5288` navy again.
  - Do this on **both** iOS and Android if the Android device is now available (the earlier iOS-only, light-only spot check is what let these through).
- No other visual/behavioural change; `php artisan test` untouched.

## Optional cleanups (low priority, same commit if trivial)
- Remove the empty leftover dirs `mobile/lib` and `mobile/assets`.
- Commit `briefs/T16.md` + `briefs/T16-fix.md` so the briefs are versioned.
