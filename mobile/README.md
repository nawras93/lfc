# LFC Mobile Workspace

Flutter pub-workspace for the LFC demo apps.

## Demo API config

Set the API base URL with `--dart-define=API_BASE_URL=...`.

- `demo_app_one` defaults to:
  - iOS simulator with `php artisan serve`: `http://localhost:8000/api/v1`
  - Android emulator with `php artisan serve`: `http://10.0.2.2:8000/api/v1`

Examples:

```bash
cd apps/demo_app_one
flutter run --dart-define=API_BASE_URL=http://localhost:8000/api/v1
```

The shared config seam lives in [app_config.dart](/Users/imahmoud/Sites/lfc/mobile/packages/lfc_core/lib/src/config/app_config.dart:1), and each thin app shell overrides its default base URL from its own `lib/config.dart`.

## Getting Started

Workspace layout:

- `packages/lfc_core` — shared app code, l10n, fonts, flags, tests
- `apps/demo_app_one` — Lusail SC app shell
- `apps/demo_app_two` — placeholder second demo app
- `apps/demo_app_three` — placeholder third demo app

A few resources to get you started if this is your first Flutter project:

- [Learn Flutter](https://docs.flutter.dev/get-started/learn-flutter)
- [Write your first Flutter app](https://docs.flutter.dev/get-started/codelab)
- [Flutter learning resources](https://docs.flutter.dev/reference/learning-resources)

For help getting started with Flutter development, view the
[online documentation](https://docs.flutter.dev/), which offers tutorials,
samples, guidance on mobile development, and a full API reference.
