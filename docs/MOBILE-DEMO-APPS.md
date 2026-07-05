# Mobile demo apps — build, run & simulator cleanup

Reference for the Flutter monorepo added in **T16**. `mobile/` is a Dart **pub workspace**: one shared package `packages/lfc_core` (~90% of the code) plus three thin per-app shells under `apps/`.

| App dir | Flutter entry | Android `applicationId` | iOS bundle id | Display name |
|---|---|---|---|---|
| `mobile/apps/demo_app_one` | `lib/main.dart` → `DemoAppOne` | `qa.lfc.app.demoone` | `qa.lfc.app.demoone` | Lusail SC |
| `mobile/apps/demo_app_two` | `lib/main.dart` → `DemoAppTwo` | `qa.lfc.app.demotwo` | `qa.lfc.app.demotwo` | Demo Two (placeholder) |
| `mobile/apps/demo_app_three` | `lib/main.dart` → `DemoAppThree` | `qa.lfc.app.demothree` | `qa.lfc.app.demothree` | Demo Three (placeholder) |

Each shell injects its **branding** (`brandProvider` → `Brand`) and **API base URL** (`appConfigProvider`) via `ProviderScope` overrides, then boots `CoreApp` from `lfc_core`. The default entry point already wires this up — **no `-t` target flag needed**. Distinct ids mean all three install side by side on one device.

---

## Prerequisites

```bash
# 1. Backend running and reachable from the device/emulator:
php artisan serve --host=0.0.0.0        # 0.0.0.0 so an emulator/physical device can reach it

# 2. One-time workspace bootstrap (resolves lfc_core + all three apps):
cd mobile
flutter pub get
```

## Run on a simulator / emulator (dev)

```bash
cd mobile/apps/demo_app_two          # or demo_app_one / demo_app_three
flutter run                          # add -d <device> to choose a target; `flutter devices` to list
```

### ⚠️ Android needs the API URL overridden
All three apps default to `http://localhost:8000/api/v1` (`apps/<app>/lib/config.dart`). Because that default is set explicitly, it **bypasses the platform-aware `10.0.2.2` fallback**, so on the **Android emulator** `localhost` resolves to the emulator itself — not your host — and the app can't reach `php artisan serve`. Override it at run time:

```bash
cd mobile/apps/demo_app_two
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1
```

iOS simulator works with the `localhost` default as-is. (`API_BASE_URL` also overrides for a physical device — point it at your machine's LAN IP.)

## Build a shippable artifact

Run from the app directory (`mobile/apps/demo_app_two`):

```bash
flutter build apk          # → build/app/outputs/flutter-apk/app-release.apk
flutter build appbundle    # → .aab for Play Store
flutter build ios          # iOS build (requires Xcode signing); or `flutter build ipa`
```

Append the same `--dart-define=API_BASE_URL=...` if the build should target something other than localhost.

> **Shipping to a real device / client?** See **[MOBILE-RELEASE-BUILDS.md](./MOBILE-RELEASE-BUILDS.md)** for the full bundling process — Android keystore signing, iOS `DEVELOPMENT_TEAM` setup + TestFlight, and the backend-URL rule for physical devices.

---

## Managing installed apps on simulators/emulators

Installed apps persist on a simulator/emulator across runs. (Note: `flutter clean` only wipes **local build output**, not the installed app.)

### iOS Simulator — `simctl`
```bash
# which sim is booted (get its UDID):
xcrun simctl list devices booted

# what's installed on the booted sim:
xcrun simctl listapps booted | grep qa.lfc.app

# uninstall by bundle id (booted = the running sim):
xcrun simctl uninstall booted qa.lfc.app.demoone
xcrun simctl uninstall booted qa.lfc.app.demotwo
xcrun simctl uninstall booted qa.lfc.app.demothree
xcrun simctl uninstall booted qa.lfc.app.mobile     # stale pre-T16 app, if present
```

### Android Emulator — `adb`
```bash
adb devices                                  # confirm an emulator is attached
adb shell pm list packages | grep lfc        # what's installed
adb uninstall qa.lfc.app.demoone
adb uninstall qa.lfc.app.demotwo
adb uninstall qa.lfc.app.demothree
# multiple devices: adb -s <serial> uninstall <package>
```

### Nuclear — wipe an entire simulator (all apps + data, factory reset)
```bash
xcrun simctl erase <UDID>     # a specific sim (from `simctl list`)
xcrun simctl erase all        # every iOS simulator
# Android: use Android Studio → Device Manager → Wipe Data, or recreate the AVD
```

> **Leftover to watch for:** the old bundle id **`qa.lfc.app.mobile`** predates the T16 rename and no longer exists in the codebase. If you built the app before T16, it may linger on a simulator as an orphaned icon — safe to uninstall with the commands above.
