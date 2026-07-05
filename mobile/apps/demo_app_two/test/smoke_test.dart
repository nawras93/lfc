import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:demo_app_two/brand.dart';
import 'package:demo_app_two/config.dart';
import 'package:lfc_core/lfc_core.dart';
import 'package:lfc_core/src/features/content/data/content_repository.dart';
import 'package:lfc_core/src/features/content/models/news_summary.dart';
import 'package:lfc_core/src/features/session/session_controller.dart';
import 'package:lfc_core/src/providers.dart';

void main() {
  testWidgets('supporter app builds with demo_app_two overrides', (
    tester,
  ) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          brandProvider.overrideWithValue(demoTwoBrand),
          appConfigProvider.overrideWithValue(
            const AppConfig(apiBaseUrl: demoTwoApiBaseUrl, appKey: 'app_two'),
          ),
          secureStorageProvider.overrideWithValue(_MemorySecureStorage()),
          contentRepositoryProvider.overrideWithValue(
            _SmokeContentRepository(),
          ),
          sessionControllerProvider.overrideWith(
            () => _SmokeSessionController(
              const SessionState(status: SessionStatus.unauthenticated),
            ),
          ),
        ],
        child: const SupporterApp(),
      ),
    );
    await tester.pumpAndSettle();

    expect(tester.takeException(), isNull);
  });
}

class _SmokeContentRepository extends ContentRepository {
  _SmokeContentRepository() : super(Dio());

  @override
  Future<List<NewsSummary>> fetchNews() async => [
    NewsSummary(
      id: 1,
      title: 'Supporter launch',
      excerpt: 'The Lusail SC supporter shell is live.',
      imageUrl: null,
      publishedAt: DateTime(2026, 7, 5),
    ),
  ];
}

class _SmokeSessionController extends SessionController {
  _SmokeSessionController(this.initialState);

  final SessionState initialState;

  @override
  SessionState build() => initialState;
}

class _MemorySecureStorage extends FlutterSecureStorage {
  _MemorySecureStorage();

  final Map<String, String> _values = <String, String>{};

  @override
  Future<String?> read({
    required String key,
    AppleOptions? iOptions,
    AndroidOptions? aOptions,
    LinuxOptions? lOptions,
    WebOptions? webOptions,
    WindowsOptions? wOptions,
    MacOsOptions? mOptions,
  }) async {
    return _values[key];
  }

  @override
  Future<void> write({
    required String key,
    required String? value,
    AppleOptions? iOptions,
    AndroidOptions? aOptions,
    LinuxOptions? lOptions,
    WebOptions? webOptions,
    WindowsOptions? wOptions,
    MacOsOptions? mOptions,
  }) async {
    if (value == null) {
      _values.remove(key);
      return;
    }

    _values[key] = value;
  }

  @override
  Future<void> delete({
    required String key,
    AppleOptions? iOptions,
    AndroidOptions? aOptions,
    LinuxOptions? lOptions,
    WebOptions? webOptions,
    WindowsOptions? wOptions,
    MacOsOptions? mOptions,
  }) async {
    _values.remove(key);
  }
}
