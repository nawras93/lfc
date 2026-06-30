import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import 'config/app_config.dart';
import 'core/api/api_exception.dart';
import 'core/storage/token_storage.dart';
import 'features/auth/data/auth_repository.dart';
import 'features/locale/locale_controller.dart';
import 'features/session/session_controller.dart';

final appConfigProvider = Provider<AppConfig>((ref) => AppConfig.fromEnvironment());

final secureStorageProvider = Provider<FlutterSecureStorage>(
  (ref) => const FlutterSecureStorage(),
);

final tokenStorageProvider = Provider<TokenStorage>(
  (ref) => TokenStorage(ref.watch(secureStorageProvider)),
);

final sessionEventsProvider = Provider<SessionEvents>((ref) {
  final events = SessionEvents();
  ref.onDispose(events.dispose);
  return events;
});

final localeControllerProvider =
    NotifierProvider<LocaleController, Locale>(LocaleController.new);

final dioProvider = Provider<Dio>((ref) {
  final config = ref.watch(appConfigProvider);
  final tokenStorage = ref.watch(tokenStorageProvider);
  final sessionEvents = ref.watch(sessionEventsProvider);

  final dio = Dio(
    BaseOptions(
      baseUrl: config.apiBaseUrl,
      headers: const {'Accept': 'application/json'},
      validateStatus: (status) => status != null && status < 500,
    ),
  );

  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await tokenStorage.readToken();
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        options.headers['Accept'] = 'application/json';
        handler.next(options);
      },
      onResponse: (response, handler) async {
        if (response.statusCode == 401) {
          await tokenStorage.clearToken();
          sessionEvents.emitUnauthorized();
        }

        if (response.statusCode != null && response.statusCode! >= 400) {
          handler.reject(
            DioException.badResponse(
              statusCode: response.statusCode!,
              requestOptions: response.requestOptions,
              response: response,
            ),
          );
          return;
        }

        handler.next(response);
      },
      onError: (error, handler) async {
        final mapped = ApiException.fromDioException(error);
        if (mapped.kind == ApiErrorKind.unauthorized) {
          await tokenStorage.clearToken();
          sessionEvents.emitUnauthorized();
        }
        handler.next(error.copyWith(error: mapped));
      },
    ),
  );

  return dio;
});

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(
    dio: ref.watch(dioProvider),
    tokenStorage: ref.watch(tokenStorageProvider),
  );
});

final sessionControllerProvider =
    NotifierProvider<SessionController, SessionState>(SessionController.new);

class SessionEvents {
  final StreamController<void> _unauthorizedController = StreamController<void>.broadcast();

  Stream<void> get unauthorizedStream => _unauthorizedController.stream;

  void emitUnauthorized() {
    if (!_unauthorizedController.isClosed) {
      _unauthorizedController.add(null);
    }
  }

  void dispose() {
    _unauthorizedController.close();
  }
}
