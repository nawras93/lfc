# LFC Mobile

Flutter scaffold for the LFC parent and VVIP demo app.

## Demo API config

Set the API base URL with `--dart-define=API_BASE_URL=...`.

- iOS simulator with `php artisan serve`: `http://localhost:8000/api/v1`
- Android emulator with `php artisan serve`: `http://10.0.2.2:8000/api/v1`

Examples:

```bash
flutter run --dart-define=API_BASE_URL=http://localhost:8000/api/v1
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1
```

The single config point is [lib/src/config/app_config.dart](/Users/imahmoud/Sites/lfc/mobile/lib/src/config/app_config.dart:1).

## Getting Started

This project is a starting point for the demo mobile application.

A few resources to get you started if this is your first Flutter project:

- [Learn Flutter](https://docs.flutter.dev/get-started/learn-flutter)
- [Write your first Flutter app](https://docs.flutter.dev/get-started/codelab)
- [Flutter learning resources](https://docs.flutter.dev/reference/learning-resources)

For help getting started with Flutter development, view the
[online documentation](https://docs.flutter.dev/), which offers tutorials,
samples, guidance on mobile development, and a full API reference.
