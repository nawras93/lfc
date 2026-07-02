import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:lfc_core/src/config/app_config.dart';
import 'package:lfc_core/src/providers.dart';

import 'helpers/fakes.dart';

void main() {
  test('parent dio sends current Accept-Language header per request', () async {
    final container = ProviderContainer(
      overrides: [
        appConfigProvider.overrideWithValue(
          const AppConfig(apiBaseUrl: 'http://localhost:8000/api/v1'),
        ),
        secureStorageProvider.overrideWithValue(MemorySecureStorage()),
      ],
    );
    addTearDown(container.dispose);

    final dio = container.read(dioProvider);
    final captured = <String?>[];
    dio.httpClientAdapter = FakeHttpClientAdapter((options) async {
      captured.add(options.headers['Accept-Language'] as String?);
      return jsonResponseBody(const {'data': []});
    });

    await dio.get('/offers');
    await container
        .read(localeControllerProvider.notifier)
        .setLocale(const Locale('ar'));
    await dio.get('/offers');

    expect(captured, ['en', 'ar']);
  });

  test('staff dio sends current Accept-Language header per request', () async {
    final container = ProviderContainer(
      overrides: [
        appConfigProvider.overrideWithValue(
          const AppConfig(apiBaseUrl: 'http://localhost:8000/api/v1'),
        ),
        secureStorageProvider.overrideWithValue(MemorySecureStorage()),
      ],
    );
    addTearDown(container.dispose);

    final dio = container.read(staffDioProvider);
    final captured = <String?>[];
    dio.httpClientAdapter = FakeHttpClientAdapter((options) async {
      captured.add(options.headers['Accept-Language'] as String?);
      return jsonResponseBody(const {'data': []});
    });

    await dio.get('/scan/fixtures/open');
    await container
        .read(localeControllerProvider.notifier)
        .setLocale(const Locale('ar'));
    await dio.get('/scan/fixtures/open');

    expect(captured, ['en', 'ar']);
  });
}
