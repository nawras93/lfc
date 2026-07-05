import 'dart:io' show Platform;

class AppConfig {
  const AppConfig({required this.apiBaseUrl, this.appKey});

  factory AppConfig.fromEnvironment({String? defaultBaseUrl, String? appKey}) {
    const override = String.fromEnvironment('API_BASE_URL');
    if (override.isNotEmpty) {
      return AppConfig(apiBaseUrl: override, appKey: appKey);
    }

    if (defaultBaseUrl != null && defaultBaseUrl.isNotEmpty) {
      return AppConfig(apiBaseUrl: defaultBaseUrl, appKey: appKey);
    }

    final host = Platform.isAndroid ? '10.0.2.2' : 'localhost';
    return AppConfig(apiBaseUrl: 'http://$host:8000/api/v1', appKey: appKey);
  }

  final String apiBaseUrl;
  final String? appKey;
}
