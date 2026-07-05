import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:lfc_core/lfc_core.dart';
import 'package:lfc_core/src/core/storage/token_storage.dart';
import 'package:lfc_core/src/features/auth/data/auth_repository.dart';
import 'package:lfc_core/src/features/auth/models/account.dart';
import 'package:lfc_core/src/features/auth/models/login_response.dart';
import 'package:lfc_core/src/features/membership/data/membership_repository.dart';
import 'package:lfc_core/src/features/membership/models/membership_benefit.dart';
import 'package:lfc_core/src/features/membership/models/membership_card.dart';
import 'package:lfc_core/src/features/membership/models/membership_tier_info.dart';
import 'package:lfc_core/src/features/players/data/player_repository.dart';
import 'package:lfc_core/src/features/players/models/point_history_entry.dart';
import 'package:lfc_core/src/features/scan/data/scan_repository.dart';
import 'package:lfc_core/src/features/scan/models/scan_token.dart';
import 'package:lfc_core/src/features/session/session_controller.dart';
import 'package:lfc_core/src/features/offers/data/offers_repository.dart';
import 'package:lfc_core/src/features/offers/models/offer_summary.dart';
import 'package:lfc_core/src/providers.dart';

import 'helpers/fakes.dart';
import 'helpers/test_brand.dart';

void main() {
  testWidgets('guest gate shows sign-in prompt and opens MemberAuthScreen', (
    tester,
  ) async {
    await _pumpSupporterApp(tester);

    await tester.tap(find.text('Membership'));
    await tester.pumpAndSettle();

    expect(find.text('Sign in to access your membership'), findsOneWidget);
    expect(find.byKey(const Key('membership-sign-in-button')), findsOneWidget);

    await tester.tap(find.byKey(const Key('membership-sign-in-button')));
    await tester.pumpAndSettle();

    expect(find.byKey(const Key('member-auth-submit')), findsOneWidget);
  });

  testWidgets('guest can navigate back from MemberAuthScreen via back button', (
    tester,
  ) async {
    await _pumpSupporterApp(tester);

    await tester.tap(find.text('Membership'));
    await tester.pumpAndSettle();

    await tester.tap(find.byKey(const Key('membership-sign-in-button')));
    await tester.pumpAndSettle();

    expect(find.byKey(const Key('member-auth-submit')), findsOneWidget);

    await tester.tap(find.byKey(const Key('member-auth-back')));
    await tester.pumpAndSettle();

    expect(find.byKey(const Key('member-auth-submit')), findsNothing);
    expect(
      find.byKey(const Key('membership-sign-in-button')),
      findsOneWidget,
    );
    expect(find.text('Sign in to access your membership'), findsOneWidget);
  });

  testWidgets('register flow calls register and lands authenticated', (
    tester,
  ) async {
    final authRepo = _FakeAuthRepository();

    await tester.pumpWidget(_pumpWithAuth(authRepository: authRepo));
    await tester.pumpAndSettle();

    await tester.tap(find.text('Membership'));
    await tester.pumpAndSettle();

    await tester.tap(find.byKey(const Key('membership-sign-in-button')));
    await tester.pumpAndSettle();

    await tester.tap(find.text('Create account'));
    await tester.pumpAndSettle();

    await tester.enterText(find.byKey(const Key('register-name')), 'New Fan');
    await tester.enterText(
      find.byKey(const Key('register-email')),
      'fan@example.com',
    );
    await tester.enterText(
      find.byKey(const Key('register-password')),
      'password123',
    );
    await tester.enterText(
      find.byKey(const Key('register-confirm-password')),
      'password123',
    );

    await tester.ensureVisible(find.byKey(const Key('member-auth-submit')));
    await tester.tap(find.byKey(const Key('member-auth-submit')));
    await tester.pumpAndSettle();

    expect(authRepo.registerCalled, isTrue);
    expect(authRepo.lastRegisterEmail, 'fan@example.com');

    // On success the auth route must pop and reveal the wallet underneath —
    // the user should not be stranded on the login form.
    expect(find.byKey(const Key('member-auth-submit')), findsNothing);
    expect(find.byKey(const Key('parent-qr')), findsOneWidget);
  });

  testWidgets('member wallet renders discount card + QR + history row', (
    tester,
  ) async {
    final scanRepo = _FakeScanRepository();
    final playerRepo = _FakePlayerRepository(
      accountTransactions: [
        PointHistoryEntry(
          id: 1,
          points: 50,
          type: 'earn',
          reason: 'Attendance scan',
          source: 'scan',
          createdAt: DateTime(2026, 7, 5),
        ),
      ],
    );

    await tester.pumpWidget(
      _pumpWithAuth(
        sessionState: const SessionState(
          status: SessionStatus.authenticated,
          account: Account(
            id: 10,
            name: 'Member Fan',
            email: 'member@example.com',
            phone: null,
            whatsapp: null,
            isVvip: false,
            accountType: 'member',
            accountBalance: 0,
            discountPercent: 2.5,
            discountCapPercent: 10,
          ),
        ),
        scanRepository: scanRepo,
        playerRepository: playerRepo,
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Membership'));
    await tester.pumpAndSettle();

    expect(find.byKey(const Key('parent-qr')), findsOneWidget);

    // Scroll down to see the discount history
    await tester.drag(
      find.byKey(const Key('discount-wallet-list')),
      const Offset(0, -500),
    );
    await tester.pumpAndSettle();

    expect(find.text('+0.5%'), findsOneWidget);
  });

  testWidgets('role routing shows VVIP card for vvip_member', (tester) async {
    await tester.pumpWidget(
      _pumpWithAuth(
        sessionState: const SessionState(
          status: SessionStatus.authenticated,
          account: Account(
            id: 20,
            name: 'VVIP Fan',
            email: 'vvip.member@example.com',
            phone: null,
            whatsapp: null,
            isVvip: true,
            accountType: 'vvip_member',
            accountBalance: 0,
          ),
        ),
        membershipRepository: _FakeMembershipRepository(
          membership: MembershipCard(
            tier: const MembershipTierInfo(
              name: 'Platinum',
              level: 2,
              accentColor: '#C8A24A',
            ),
            memberNumber: 'LSC-000123',
            validUntil: DateTime(2027, 6, 30),
            benefits: const [
              MembershipBenefit(
                title: 'Private lounge',
                description: 'Access on home match days',
                icon: 'lounge',
              ),
            ],
          ),
        ),
        offersRepository: _FakeOffersRepository(),
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Membership'));
    await tester.pumpAndSettle();

    await tester.ensureVisible(
      find.text('Private lounge', skipOffstage: false),
    );
    await tester.pumpAndSettle();

    expect(find.text('Platinum'), findsOneWidget);
    expect(find.text('LSC-000123'), findsOneWidget);
    expect(find.byKey(const Key('vvip-qr')), findsOneWidget);
    expect(find.text('Private lounge'), findsOneWidget);
  });

  testWidgets('VVIP offers section renders VVIP offer card', (tester) async {
    await tester.pumpWidget(
      _pumpWithAuth(
        sessionState: const SessionState(
          status: SessionStatus.authenticated,
          account: Account(
            id: 21,
            name: 'VVIP Fan',
            email: 'vvip.member@example.com',
            phone: null,
            whatsapp: null,
            isVvip: true,
            accountType: 'vvip_member',
            accountBalance: 0,
          ),
        ),
        membershipRepository: _FakeMembershipRepository(
          membership: MembershipCard(
            tier: const MembershipTierInfo(
              name: 'Gold',
              level: 1,
              accentColor: '#C8A24A',
            ),
            memberNumber: 'LSC-000777',
            validUntil: DateTime(2027, 6, 30),
            benefits: const [],
          ),
        ),
        offersRepository: _FakeOffersRepository(
          offers: const [
            OfferSummary(
              id: 1,
              title: 'VVIP Lounge',
              body: 'Private hospitality access',
              audience: 'vvip',
              validFrom: null,
              validUntil: null,
            ),
          ],
        ),
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Membership'));
    await tester.pumpAndSettle();

    await tester.ensureVisible(find.text('VVIP offers', skipOffstage: false));
    await tester.pumpAndSettle();

    expect(find.text('VVIP offers'), findsOneWidget);
    expect(find.text('VVIP Lounge'), findsOneWidget);
    expect(find.text('VVIP'), findsAtLeastNWidgets(1));
  });

  testWidgets('VVIP card handles missing membership gracefully', (
    tester,
  ) async {
    await tester.pumpWidget(
      _pumpWithAuth(
        sessionState: const SessionState(
          status: SessionStatus.authenticated,
          account: Account(
            id: 22,
            name: 'VVIP Fan',
            email: 'vvip.member@example.com',
            phone: null,
            whatsapp: null,
            isVvip: true,
            accountType: 'vvip_member',
            accountBalance: 0,
          ),
        ),
        membershipRepository: _FakeMembershipRepository(membership: null),
        offersRepository: _FakeOffersRepository(),
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Membership'));
    await tester.pumpAndSettle();

    expect(find.text('Membership details unavailable'), findsOneWidget);
  });

  testWidgets('member wallet shows discount card for member account', (
    tester,
  ) async {
    await tester.pumpWidget(
      _pumpWithAuth(
        sessionState: const SessionState(
          status: SessionStatus.authenticated,
          account: Account(
            id: 30,
            name: 'Regular Fan',
            email: 'fan@lfc.test',
            phone: null,
            whatsapp: null,
            isVvip: false,
            accountType: 'member',
            accountBalance: 0,
            discountPercent: 5,
            discountCapPercent: 10,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Membership'));
    await tester.pumpAndSettle();

    expect(find.text('ACADEMY REGISTRATION DISCOUNT'), findsOneWidget);
    expect(find.text('5.0%'), findsOneWidget);
    expect(find.text('of 10% maximum'), findsOneWidget);
  });

  testWidgets('member wallet renders RTL after switching to Arabic', (
    tester,
  ) async {
    await tester.pumpWidget(
      _pumpWithAuth(
        sessionState: const SessionState(
          status: SessionStatus.authenticated,
          account: Account(
            id: 40,
            name: 'مشجع',
            email: 'fan@lfc.test',
            phone: null,
            whatsapp: null,
            isVvip: false,
            accountType: 'member',
            accountBalance: 0,
            discountPercent: 3,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Membership'));
    await tester.pumpAndSettle();

    await tester.tap(find.byKey(const Key('language-toggle')).first);
    await tester.pumpAndSettle();

    expect(find.text('خصم التسجيل في الأكاديمية'), findsOneWidget);
  });

  testWidgets('VVIP card renders RTL after switching to Arabic', (
    tester,
  ) async {
    await tester.pumpWidget(
      _pumpWithAuth(
        sessionState: const SessionState(
          status: SessionStatus.authenticated,
          account: Account(
            id: 41,
            name: 'مشجع مميز',
            email: 'vvip@lfc.test',
            phone: null,
            whatsapp: null,
            isVvip: true,
            accountType: 'vvip_member',
            accountBalance: 0,
          ),
        ),
        membershipRepository: _FakeMembershipRepository(
          membership: MembershipCard(
            tier: const MembershipTierInfo(
              name: 'بلاتيني',
              level: 2,
              accentColor: '#C8A24A',
            ),
            memberNumber: 'LSC-AR-9',
            validUntil: DateTime(2027, 6, 30),
            benefits: const [
              MembershipBenefit(
                title: 'الدخول إلى الصالة',
                description: 'ضيافة خاصة قبل المباراة',
                icon: 'lounge',
              ),
            ],
          ),
        ),
        offersRepository: _FakeOffersRepository(),
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Membership'));
    await tester.pumpAndSettle();
    await tester.tap(find.byKey(const Key('language-toggle')).first);
    await tester.pumpAndSettle();
    await tester.ensureVisible(
      find.text('الدخول إلى الصالة', skipOffstage: false),
    );
    await tester.pumpAndSettle();

    expect(find.text('بطاقة العضوية'), findsOneWidget);
    expect(find.text('مزاياك'), findsOneWidget);
    expect(find.text('الدخول إلى الصالة'), findsOneWidget);
  });

  testWidgets('VVIP card refetches benefits + offers on language switch', (
    tester,
  ) async {
    final membershipRepo = _FakeMembershipRepository(
      membership: MembershipCard(
        tier: const MembershipTierInfo(
          name: 'Platinum',
          level: 2,
          accentColor: '#C8A24A',
        ),
        memberNumber: 'LSC-000123',
        validUntil: DateTime(2027, 6, 30),
        benefits: const [
          MembershipBenefit(
            title: 'Private lounge',
            description: 'Access on home match days',
            icon: 'lounge',
          ),
        ],
      ),
    );
    final offersRepo = _FakeOffersRepository(
      offers: const [
        OfferSummary(
          id: 1,
          title: 'VVIP Lounge',
          body: 'Private hospitality access',
          audience: 'vvip',
          validFrom: null,
          validUntil: null,
        ),
      ],
    );

    await tester.pumpWidget(
      _pumpWithAuth(
        sessionState: const SessionState(
          status: SessionStatus.authenticated,
          account: Account(
            id: 24,
            name: 'VVIP Fan',
            email: 'vvip.member@example.com',
            phone: null,
            whatsapp: null,
            isVvip: true,
            accountType: 'vvip_member',
            accountBalance: 0,
          ),
        ),
        membershipRepository: membershipRepo,
        offersRepository: offersRepo,
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Membership'));
    await tester.pumpAndSettle();

    // Card is displayed → both server-localized providers fetched at least once.
    final membershipBefore = membershipRepo.fetchCount;
    final offersBefore = offersRepo.fetchCount;
    expect(membershipBefore, greaterThanOrEqualTo(1));
    expect(offersBefore, greaterThanOrEqualTo(1));

    // Switching language must refetch them (otherwise stale English content).
    await tester.tap(find.byKey(const Key('language-toggle')).first);
    await tester.pumpAndSettle();

    expect(membershipRepo.fetchCount, greaterThan(membershipBefore));
    expect(offersRepo.fetchCount, greaterThan(offersBefore));
  });
}

Future<void> _pumpSupporterApp(WidgetTester tester) async {
  tester.view.physicalSize = const Size(800, 600);
  tester.view.devicePixelRatio = 1;
  addTearDown(() {
    tester.view.resetPhysicalSize();
    tester.view.resetDevicePixelRatio();
  });

  await tester.pumpWidget(
    ProviderScope(
      overrides: [
        appConfigProvider.overrideWithValue(
          const AppConfig(
            apiBaseUrl: 'http://localhost:8000/api/v1',
            appKey: 'app_two',
          ),
        ),
        brandProvider.overrideWithValue(testBrand),
        secureStorageProvider.overrideWithValue(MemorySecureStorage()),
        sessionControllerProvider.overrideWith(
          () => _FakeSessionController(
            const SessionState(status: SessionStatus.unauthenticated),
          ),
        ),
        playerRepositoryProvider.overrideWithValue(_FakePlayerRepository()),
        parentScanRepositoryProvider.overrideWithValue(_FakeScanRepository()),
      ],
      child: const SupporterApp(),
    ),
  );
  await tester.pumpAndSettle();
}

Widget _pumpWithAuth({
  SessionState sessionState = const SessionState(
    status: SessionStatus.unauthenticated,
  ),
  AuthRepository? authRepository,
  PlayerRepository? playerRepository,
  ScanRepository? scanRepository,
  MembershipRepository? membershipRepository,
  OffersRepository? offersRepository,
}) {
  final authRepo = authRepository ?? _FakeAuthRepository();
  final scanRepo = scanRepository ?? _FakeScanRepository();
  final playerRepo = playerRepository ?? _FakePlayerRepository();
  final membershipRepo = membershipRepository ?? _FakeMembershipRepository();
  final offersRepo = offersRepository ?? _FakeOffersRepository();

  return ProviderScope(
    overrides: [
      appConfigProvider.overrideWithValue(
        const AppConfig(
          apiBaseUrl: 'http://localhost:8000/api/v1',
          appKey: 'app_two',
        ),
      ),
      brandProvider.overrideWithValue(testBrand),
      secureStorageProvider.overrideWithValue(MemorySecureStorage()),
      authRepositoryProvider.overrideWithValue(authRepo),
      sessionControllerProvider.overrideWith(
        () => _FakeSessionController(sessionState, authRepo),
      ),
      playerRepositoryProvider.overrideWithValue(playerRepo),
      membershipRepositoryProvider.overrideWithValue(membershipRepo),
      offersRepositoryProvider.overrideWithValue(offersRepo),
      accountTransactionsProvider.overrideWith(
        (ref) => playerRepo.fetchAccountTransactions(),
      ),
      parentScanRepositoryProvider.overrideWithValue(scanRepo),
    ],
    child: const SupporterApp(),
  );
}

class _FakeAuthRepository extends AuthRepository {
  _FakeAuthRepository()
    : super(dio: Dio(), tokenStorage: TokenStorage(MemorySecureStorage()));

  bool registerCalled = false;
  String? lastRegisterEmail;

  @override
  Future<LoginResponse> login({
    required String email,
    required String password,
  }) async {
    return LoginResponse(
      token: 'token',
      account: const Account(
        id: 1,
        name: 'Fan',
        email: 'fan@example.com',
        phone: null,
        whatsapp: null,
        isVvip: false,
        accountType: 'member',
        accountBalance: 0,
      ),
    );
  }

  @override
  Future<LoginResponse> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
  }) async {
    registerCalled = true;
    lastRegisterEmail = email;
    return LoginResponse(
      token: 'register-token',
      account: Account(
        id: 2,
        name: name,
        email: email,
        phone: phone,
        whatsapp: null,
        isVvip: false,
        accountType: 'member',
        accountBalance: 0,
      ),
    );
  }

  @override
  Future<Account> getMe() async {
    return const Account(
      id: 1,
      name: 'Fan',
      email: 'fan@example.com',
      phone: null,
      whatsapp: null,
      isVvip: false,
      accountType: 'member',
      accountBalance: 0,
    );
  }

  @override
  Future<void> logout() async {}

  @override
  Future<String?> readStoredToken() async => null;

  @override
  Future<void> clearToken() async {}
}

class _FakeSessionController extends SessionController {
  _FakeSessionController(this.initialState, [this.authRepository]);

  final SessionState initialState;
  final AuthRepository? authRepository;

  @override
  SessionState build() => initialState;

  @override
  Future<void> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
  }) async {
    final repo = authRepository!;
    final result = await repo.register(
      name: name,
      email: email,
      password: password,
      passwordConfirmation: passwordConfirmation,
      phone: phone,
    );
    state = SessionState(
      status: SessionStatus.authenticated,
      token: result.token,
      account: result.account,
    );
  }

  @override
  Future<void> login({required String email, required String password}) async {
    final repo = authRepository!;
    final result = await repo.login(email: email, password: password);
    state = SessionState(
      status: SessionStatus.authenticated,
      token: result.token,
      account: result.account,
    );
  }

  @override
  Future<void> logout() async {
    state = const SessionState(status: SessionStatus.unauthenticated);
  }
}

class _FakePlayerRepository extends PlayerRepository {
  _FakePlayerRepository({this.accountTransactions = const []}) : super(Dio());

  final List<PointHistoryEntry> accountTransactions;

  @override
  Future<List<PointHistoryEntry>> fetchAccountTransactions() async =>
      accountTransactions;
}

class _FakeScanRepository extends ScanRepository {
  _FakeScanRepository() : super(Dio());

  @override
  Future<ScanToken> fetchParentToken() async {
    return ScanToken(
      token: 'test-token',
      expiresAt: DateTime.now().add(const Duration(minutes: 5)),
    );
  }
}

class _FakeMembershipRepository extends MembershipRepository {
  _FakeMembershipRepository({this.membership}) : super(Dio());

  final MembershipCard? membership;
  int fetchCount = 0;

  @override
  Future<MembershipCard?> fetchMembership() async {
    fetchCount++;
    return membership;
  }
}

class _FakeOffersRepository extends OffersRepository {
  _FakeOffersRepository({this.offers = const []}) : super(Dio());

  final List<OfferSummary> offers;
  int fetchCount = 0;

  @override
  Future<List<OfferSummary>> fetchOffers() async {
    fetchCount++;
    return offers;
  }
}
