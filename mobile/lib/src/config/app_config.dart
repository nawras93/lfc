import 'dart:io' show Platform;

class AppConfig {
  const AppConfig({required this.apiBaseUrl});

  factory AppConfig.fromEnvironment() {
    const override = String.fromEnvironment('API_BASE_URL');
    if (override.isNotEmpty) {
      return const AppConfig(apiBaseUrl: override);
    }

    final host = Platform.isAndroid ? '10.0.2.2' : 'localhost';

    return AppConfig(apiBaseUrl: 'http://$host:8000/api/v1');
  }

  final String apiBaseUrl;
}
