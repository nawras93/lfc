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
import 'features/locale/data/locale_storage.dart';
import 'features/offers/data/offers_repository.dart';
import 'features/players/data/player_repository.dart';
import 'features/players/models/player_summary.dart';
import 'features/redemptions/data/redemption_repository.dart';
import 'features/scan/data/scan_repository.dart';
import 'features/session/session_controller.dart';
import 'features/staff/data/staff_session_storage.dart';
import 'features/staff/staff_session_controller.dart';
import 'theme/data/theme_storage.dart';
import 'theme/theme_controller.dart';

final appConfigProvider = Provider<AppConfig>(
  (ref) => AppConfig.fromEnvironment(),
);

final secureStorageProvider = Provider<FlutterSecureStorage>(
  (ref) => const FlutterSecureStorage(),
);

final tokenStorageProvider = Provider<TokenStorage>(
  (ref) => TokenStorage(ref.watch(secureStorageProvider)),
);

final localeStorageProvider = Provider<LocaleStorage>(
  (ref) => LocaleStorage(ref.watch(secureStorageProvider)),
);

final staffSessionStorageProvider = Provider<StaffSessionStorage>(
  (ref) => StaffSessionStorage(ref.watch(secureStorageProvider)),
);

final sessionEventsProvider = Provider<SessionEvents>((ref) {
  final events = SessionEvents();
  ref.onDispose(events.dispose);
  return events;
});

final localeControllerProvider = NotifierProvider<LocaleController, Locale>(
  LocaleController.new,
);

final themeStorageProvider = Provider<ThemeStorage>(
  (ref) => ThemeStorage(ref.watch(secureStorageProvider)),
);

final themeControllerProvider = NotifierProvider<ThemeController, ThemeMode>(
  ThemeController.new,
);

final staffSessionEventsProvider = Provider<SessionEvents>((ref) {
  final events = SessionEvents();
  ref.onDispose(events.dispose);
  return events;
});

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

final playerRepositoryProvider = Provider<PlayerRepository>(
  (ref) => PlayerRepository(ref.watch(dioProvider)),
);

final playersProvider = FutureProvider.autoDispose<List<PlayerSummary>>(
  (ref) => ref.watch(playerRepositoryProvider).fetchPlayers(),
);

final redemptionRepositoryProvider = Provider<RedemptionRepository>(
  (ref) => RedemptionRepository(ref.watch(dioProvider)),
);

final offersRepositoryProvider = Provider<OffersRepository>(
  (ref) => OffersRepository(ref.watch(dioProvider)),
);

final parentScanRepositoryProvider = Provider<ScanRepository>(
  (ref) => ScanRepository(ref.watch(dioProvider)),
);

final staffDioProvider = Provider<Dio>((ref) {
  final config = ref.watch(appConfigProvider);
  final storage = ref.watch(staffSessionStorageProvider);
  final sessionEvents = ref.watch(staffSessionEventsProvider);

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
        final token = await storage.readToken();
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        options.headers['Accept'] = 'application/json';
        handler.next(options);
      },
      onResponse: (response, handler) async {
        if (response.statusCode == 401) {
          await storage.clear();
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
          await storage.clear();
          sessionEvents.emitUnauthorized();
        }
        handler.next(error.copyWith(error: mapped));
      },
    ),
  );

  return dio;
});

final staffSessionControllerProvider =
    NotifierProvider<StaffSessionController, StaffSessionState>(
      StaffSessionController.new,
    );

final staffScanRepositoryProvider = Provider<ScanRepository>(
  (ref) => ScanRepository(ref.watch(staffDioProvider)),
);

class SessionEvents {
  final StreamController<void> _unauthorizedController =
      StreamController<void>.broadcast();

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
