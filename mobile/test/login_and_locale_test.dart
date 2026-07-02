import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/src/app.dart';
import 'package:mobile/src/config/app_config.dart';
import 'package:mobile/src/core/storage/token_storage.dart';
import 'package:mobile/src/features/auth/data/auth_repository.dart';
import 'package:mobile/src/features/auth/models/account.dart';
import 'package:mobile/src/features/auth/models/login_response.dart';
import 'package:mobile/src/features/auth/models/staff_user.dart';
import 'package:mobile/src/features/auth/models/staff_login_response.dart';
import 'package:mobile/src/features/offers/data/offers_repository.dart';
import 'package:mobile/src/features/offers/models/offer_summary.dart';
import 'package:mobile/src/features/players/data/player_repository.dart';
import 'package:mobile/src/features/players/models/player_summary.dart';
import 'package:mobile/src/features/players/models/point_history_entry.dart';
import 'package:mobile/src/features/redemptions/data/redemption_repository.dart';
import 'package:mobile/src/features/redemptions/models/redemption_history_item.dart';
import 'package:mobile/src/features/redemptions/models/redemption_item_summary.dart';
import 'package:mobile/src/features/redemptions/models/redemption_voucher.dart';
import 'package:mobile/src/features/scan/data/scan_repository.dart';
import 'package:mobile/src/features/scan/models/fixture_summary.dart';
import 'package:mobile/src/features/scan/models/scan_result.dart';
import 'package:mobile/src/features/scan/models/scan_token.dart';
import 'package:mobile/src/features/session/session_controller.dart';
import 'package:mobile/src/providers.dart';

import 'helpers/fakes.dart';

void main() {
  testWidgets('login screen validates required fields', (tester) async {
    await tester.pumpWidget(_testApp());
    await tester.pumpAndSettle();

    await tester.tap(find.byKey(const Key('login-submit')));
    await tester.pump();

    expect(find.text('This field is required.'), findsNWidgets(2));
  });

  testWidgets('login screen accepts .test email addresses', (tester) async {
    await tester.pumpWidget(_testApp());
    await tester.pumpAndSettle();

    await tester.enterText(
      find.byKey(const Key('login-email')),
      'parent.demo@lfc.test',
    );
    await tester.enterText(find.byKey(const Key('login-password')), 'password');
    await tester.tap(find.byKey(const Key('login-submit')));
    await tester.pumpAndSettle();

    expect(find.text('Enter a valid email address.'), findsNothing);
  });

  testWidgets('language toggle switches locale and home shell becomes RTL', (
    tester,
  ) async {
    await tester.pumpWidget(
      _testApp(
        sessionOverride: const SessionState(
          status: SessionStatus.authenticated,
          account: Account(
            id: 1,
            name: 'أحمد',
            email: 'vip@example.com',
            phone: null,
            whatsapp: null,
            isVvip: true,
            accountType: 'vvip_client',
            accountBalance: 120,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(
      Directionality.of(tester.element(find.text('Players').first)),
      TextDirection.ltr,
    );

    await tester.tap(find.byKey(const Key('language-toggle')).first);
    await tester.pumpAndSettle();

    expect(find.text('اللاعبون'), findsWidgets);
    expect(
      Directionality.of(tester.element(find.text('اللاعبون').first)),
      TextDirection.rtl,
    );
  });
}

Widget _testApp({SessionState? sessionOverride}) {
  final fakeRepository = _FakeAuthRepository();

  return ProviderScope(
    overrides: [
      appConfigProvider.overrideWithValue(
        const AppConfig(apiBaseUrl: 'http://localhost:8000/api/v1'),
      ),
      secureStorageProvider.overrideWithValue(MemorySecureStorage()),
      authRepositoryProvider.overrideWithValue(fakeRepository),
      playerRepositoryProvider.overrideWithValue(_FakePlayerRepository()),
      offersRepositoryProvider.overrideWithValue(_FakeOffersRepository()),
      redemptionRepositoryProvider.overrideWithValue(
        _FakeRedemptionRepository(),
      ),
      parentScanRepositoryProvider.overrideWithValue(_FakeScanRepository()),
      staffScanRepositoryProvider.overrideWithValue(_FakeScanRepository()),
      if (sessionOverride != null)
        sessionControllerProvider.overrideWith(
          () => _FakeSessionController(sessionOverride, fakeRepository),
        ),
    ],
    child: const LfcApp(),
  );
}

class _FakeAuthRepository extends AuthRepository {
  _FakeAuthRepository()
    : super(dio: Dio(), tokenStorage: TokenStorage(MemorySecureStorage()));

  Account account = const Account(
    id: 1,
    name: 'Parent Demo',
    email: 'parent@example.com',
    phone: null,
    whatsapp: null,
    isVvip: false,
    accountType: 'parent',
    accountBalance: 0,
  );

  @override
  Future<LoginResponse> login({
    required String email,
    required String password,
  }) async {
    return LoginResponse(token: 'token', account: account);
  }

  @override
  Future<Account> getMe() async => account;

  @override
  Future<StaffLoginResponse> staffLogin({
    required String email,
    required String password,
  }) async {
    return const StaffLoginResponse(
      token: 'staff-token',
      user: StaffUser(id: 1, name: 'Staff', email: 'staff@example.com'),
    );
  }

  @override
  Future<void> logout() async {}

  @override
  Future<String?> readStoredToken() async => null;

  @override
  Future<void> clearToken() async {}
}

class _FakePlayerRepository extends PlayerRepository {
  _FakePlayerRepository() : super(Dio());

  @override
  Future<List<PlayerSummary>> fetchPlayers() async => const [];

  @override
  Future<List<PointHistoryEntry>> fetchPlayerTransactions(int playerId) async =>
      const [];

  @override
  Future<List<PointHistoryEntry>> fetchAccountTransactions() async => const [];
}

class _FakeOffersRepository extends OffersRepository {
  _FakeOffersRepository() : super(Dio());

  @override
  Future<List<OfferSummary>> fetchOffers() async => const [];
}

class _FakeRedemptionRepository extends RedemptionRepository {
  _FakeRedemptionRepository() : super(Dio());

  @override
  Future<List<RedemptionItemSummary>> fetchItems() async => const [];

  @override
  Future<List<RedemptionHistoryItem>> fetchHistory() async => const [];

  @override
  Future<RedemptionVoucher> redeem({
    required int redemptionItemId,
    int? playerId,
  }) async {
    throw UnimplementedError();
  }
}

class _FakeScanRepository extends ScanRepository {
  _FakeScanRepository() : super(Dio());

  @override
  Future<ScanToken> fetchParentToken() async {
    return ScanToken(
      token: 'token',
      expiresAt: DateTime.now().add(const Duration(minutes: 1)),
    );
  }

  @override
  Future<List<FixtureSummary>> fetchOpenFixtures() async => const [];

  @override
  Future<ScanResult> submitScan({
    required int fixtureId,
    required String token,
  }) async {
    throw UnimplementedError();
  }
}

class _FakeSessionController extends SessionController {
  _FakeSessionController(this.initialState, this.repository);

  final SessionState initialState;
  final AuthRepository repository;

  @override
  SessionState build() => initialState;

  @override
  Future<void> clearSession() async {
    state = const SessionState(status: SessionStatus.unauthenticated);
  }

  @override
  Future<void> login({required String email, required String password}) async {
    final response = await repository.login(email: email, password: password);
    state = SessionState(
      status: SessionStatus.authenticated,
      token: response.token,
      account: response.account,
    );
  }

  @override
  Future<void> acceptInvite({
    required String token,
    required String password,
  }) async {}

  @override
  Future<void> logout() async {
    state = const SessionState(status: SessionStatus.unauthenticated);
  }
}
