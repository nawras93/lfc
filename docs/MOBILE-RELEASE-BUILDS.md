# Mobile release & bundling — Android & iOS

How to turn the Flutter demo apps into installable artifacts for a **real device** (client demo / device testing). For dev runs on a simulator/emulator and app-cleanup commands, see **[MOBILE-DEMO-APPS.md](./MOBILE-DEMO-APPS.md)**.

`mobile/` is a Dart pub workspace: shared `packages/lfc_core` + three per-app shells. **Every command below is run from the individual app directory** — you bundle one app at a time.

| App dir | Android `applicationId` | iOS bundle id |
|---|---|---|
| `mobile/apps/demo_app_one` | `qa.lfc.app.demoone` | `qa.lfc.app.demoone` |
| `mobile/apps/demo_app_two` | `qa.lfc.app.demotwo` | `qa.lfc.app.demotwo` |
| `mobile/apps/demo_app_three` | `qa.lfc.app.demothree` | `qa.lfc.app.demothree` |

Distinct ids ⇒ all three can be installed side by side on one device.

> Verified against **Flutter 3.44.4 (stable)**.

---

## 0. Pre-flight checklist (do this once, before the first real-device build)

| Item | Status in repo today | Needed for… |
|---|---|---|
| Unique bundle ids | ✅ set per app (table above) | everything |
| **Backend reachable over the network** | ⚠️ apps default to `localhost` | any real-device build |
| Android release keystore | ❌ release is **debug-signed** (`signingConfigs.getByName("debug")`) | Play Store / production only |
| iOS `DEVELOPMENT_TEAM` | ❌ **not set** (`CODE_SIGN_STYLE = Automatic`, no team) | any iOS device / TestFlight build |
| Distinct home-screen names | ⚠️ `demo_app_one` **and** `demo_app_two` both show **"Lusail SC"**; `demo_app_three` is **"Demo Three"** | avoid indistinguishable icons on one device |

### The backend-URL rule (applies to BOTH platforms)
Every app hard-defaults to `http://localhost:8000/api/v1` (`apps/<app>/lib/config.dart`). On a physical device `localhost` is the **phone itself**, so the API won't respond. You **must** override the base URL at build time:

```
--dart-define=API_BASE_URL=https://<host-the-phone-can-reach>/api/v1
```

Choose a host:
- **Same Wi-Fi:** your Mac's LAN IP, e.g. `http://192.168.1.20:8000/api/v1` (run the backend with `php artisan serve --host=0.0.0.0`).
- **Anywhere / shareable:** a tunnel (`ngrok http 8000`) or a deployed staging server → an `https://…` URL.

> **⚠️ iOS App Transport Security:** iOS **blocks plain `http://`** by default, so a bare LAN IP over `http` normally fails on a real iPhone. **All three apps ship with an ATS exception** (`NSAllowsArbitraryLoads` in each `ios/Runner/Info.plist`), so they can hit an `http://<mac-ip>:8000` backend directly — no tunnel needed for a demo. This is a demo-only relaxation; tighten or remove it before any App Store submission. Android has no such restriction.

---

## 1. Android

### 1a. Demo APK (debug-signed — zero setup, install today)
Good enough to hand someone an APK to sideload. Works out of the box because the release build type currently signs with the debug key.

```bash
cd mobile/apps/demo_app_two          # or demo_app_one / demo_app_three
flutter build apk --release --dart-define=API_BASE_URL=https://your-host/api/v1
```

Output:
```
mobile/apps/demo_app_two/build/app/outputs/flutter-apk/app-release.apk
```

Send that `.apk`; the tester enables **Install unknown apps** for their browser/file manager and taps it. Done.

### 1b. Production build (real keystore — for Play Store)
Only needed if the app goes to the Play Store; skip for sideloaded demos. The Google Play Store rejects debug-signed builds.

1. **Create a keystore** (one shared keystore can sign all three apps):
   ```bash
   keytool -genkey -v -keystore ~/lfc-demo-upload.jks \
     -keyalg RSA -keysize 2048 -validity 10000 -alias lfc-demo
   ```
2. **Add `mobile/apps/<app>/android/key.properties`** (already git-ignored — do **not** commit it or the `.jks`):
   ```properties
   storePassword=<password>
   keyPassword=<password>
   keyAlias=lfc-demo
   storeFile=/Users/you/lfc-demo-upload.jks
   ```
3. **Wire it into `android/app/build.gradle.kts`** — load the properties at the top and add a `release` signing config:
   ```kotlin
   import java.util.Properties
   import java.io.FileInputStream

   val keystoreProperties = Properties()
   val keystorePropertiesFile = rootProject.file("key.properties")
   if (keystorePropertiesFile.exists()) {
       keystoreProperties.load(FileInputStream(keystorePropertiesFile))
   }

   android {
       // …
       signingConfigs {
           create("release") {
               keyAlias = keystoreProperties["keyAlias"] as String?
               keyPassword = keystoreProperties["keyPassword"] as String?
               storeFile = (keystoreProperties["storeFile"] as String?)?.let { file(it) }
               storePassword = keystoreProperties["storePassword"] as String?
           }
       }
       buildTypes {
           release {
               signingConfig = signingConfigs.getByName("release")   // replaces the debug default
           }
       }
   }
   ```
   > `rootProject.file("key.properties")` resolves to `android/key.properties` in the Flutter layout.
4. **Build the Play Store bundle:**
   ```bash
   flutter build appbundle --release --dart-define=API_BASE_URL=https://your-host/api/v1
   # → build/app/outputs/bundle/release/app-release.aab
   ```

---

## 2. iOS

There is **no APK equivalent** — you cannot email an `.ipa` for one-tap install. iOS distribution requires an **Apple Developer Program membership ($99/yr)** and code signing. **TestFlight is the recommended path for a remote client.**

### 2a. One-time signing setup (currently missing — blocks all device builds)
The projects use `CODE_SIGN_STYLE = Automatic` but have **no `DEVELOPMENT_TEAM`**, so device/IPA builds fail until you assign a team:

1. Open the app's workspace in Xcode:
   ```bash
   open mobile/apps/demo_app_two/ios/Runner.xcworkspace
   ```
2. Select the **Runner** target → **Signing & Capabilities** → tick *Automatically manage signing* → pick your **Team**. Xcode registers the bundle id in the Apple Developer portal.
3. Repeat for each app you're shipping (each has its own bundle id).

> Simulator builds do **not** need this — only real-device and TestFlight/ad-hoc builds do.

### 2b. TestFlight (recommended for client testing)
```bash
cd mobile/apps/demo_app_two
flutter build ipa --release --dart-define=API_BASE_URL=https://your-host/api/v1
# → build/ios/ipa/*.ipa
```
Then upload to App Store Connect via **Xcode → Organizer** (opens automatically) or the **Transporter** app. Add the client's email as an **external tester**; they install through Apple's **TestFlight** app. Builds last ~90 days.

### 2c. Ad-hoc (a few known devices, no App Store)
For a handful of specific iPhones without going through TestFlight:
1. Collect each device's **UDID** and register them in the Apple Developer portal.
2. Build with an ad-hoc export (via Xcode Organizer → *Distribute App → Ad Hoc*, or a matching `ExportOptions.plist`).
3. Distribute the resulting `.ipa` via a link service (e.g. Firebase App Distribution / Diawi).

### 2d. Cabled dev build (devices physically with you)
Fastest if the iPhone is plugged into the Mac, and works with a **free** Apple ID (app expires after 7 days). Full step-by-step in **[§4](#4-run-directly-on-a-usb-cabled-device-fastest-for-hands-on-testing)** below.

---

## 3. Cross-platform distribution helper (optional)
**Firebase App Distribution** hosts both the Android APK and the iOS build behind an email-invite + install link, and manages the tester list — nicer than passing raw files. iOS still needs the ad-hoc/TestFlight signing underneath.

---

## 4. Run directly on a USB-cabled device (fastest for hands-on testing)

No packaging or store step — `flutter run` compiles, installs, and launches straight onto the tethered phone. Best when the device is in your hand. Requires a **Mac with Xcode** for iPhone; Android works from any OS.

**Backend first:** start the API bound to all interfaces so the phone can reach your machine, and note your Mac's LAN IP:
```bash
php artisan serve --host=0.0.0.0        # from the Laravel project root
ipconfig getifaddr en0                  # your Mac's Wi-Fi IP, e.g. 192.168.1.20
```
Both phone and Mac must be on the **same Wi-Fi network**.

### 4a. Android over USB (no signing setup)
1. On the phone: **Settings → About phone → tap Build number 7×** to unlock Developer options, then enable **USB debugging** under Developer options.
2. Plug in via USB and tap **Allow** on the "Allow USB debugging?" prompt.
3. From the app dir, find the device id and run — Android permits plain `http`, so point at the Mac's LAN IP:
   ```bash
   cd mobile/apps/demo_app_two
   flutter devices                       # copy the phone's id (not the emulator)
   flutter run --release -d <device-id> \
     --dart-define=API_BASE_URL=http://<mac-lan-ip>:8000/api/v1
   ```

> `adb` isn't required on your PATH — Flutter uses its bundled copy. (For raw `adb` commands, add the SDK's `platform-tools` to PATH.)

### 4b. iPhone over USB (free Apple ID)
Works with a personal Apple ID — no $99 membership needed — but the installed app **stops working after 7 days**; just re-run to reinstall.

1. **Assign a signing team** (one-time — the projects ship with none). Open the workspace and set it in Xcode:
   ```bash
   open mobile/apps/demo_app_two/ios/Runner.xcworkspace
   ```
   Runner target → **Signing & Capabilities** → *Automatically manage signing* → sign in with your Apple ID → pick your personal team. (See [§2a](#2a-one-time-signing-setup-currently-missing--blocks-all-device-builds).)
2. **On the iPhone (iOS 16+):** enable **Settings → Privacy & Security → Developer Mode** and reboot when prompted.
3. Plug in via USB, unlock the phone, and tap **Trust This Computer**.
4. Run it:
   ```bash
   cd mobile/apps/demo_app_two
   flutter devices                       # copy the physical iPhone's id (not the simulator)
   flutter run --release -d <iphone-device-id> \
     --dart-define=API_BASE_URL=http://<mac-lan-ip>:8000/api/v1
   ```
5. **First launch shows "Untrusted Developer"** — go to **Settings → General → VPN & Device Management**, tap your developer profile, and **Trust** it. Relaunch the app.

> **All three apps can use a plain `http://<mac-ip>` URL** — each `Info.plist` carries the demo-only ATS exception. (Prefer an `https://` tunnel/staging URL only if you want to test with ATS enforced as in production.)

---

## Quick reference

| Goal | Command (from `mobile/apps/<app>`) | Output |
|---|---|---|
| Android demo APK | `flutter build apk --release --dart-define=API_BASE_URL=…` | `build/app/outputs/flutter-apk/app-release.apk` |
| Android Play Store bundle | `flutter build appbundle --release --dart-define=API_BASE_URL=…` | `build/app/outputs/bundle/release/app-release.aab` |
| iOS IPA (TestFlight/ad-hoc) | `flutter build ipa --release --dart-define=API_BASE_URL=…` | `build/ios/ipa/*.ipa` |

**Always append** `--dart-define=API_BASE_URL=<reachable-url>` — a release build with the `localhost` default cannot reach the backend on a real device.
