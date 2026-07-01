import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/src/app.dart';
import 'package:mobile/src/config/app_config.dart';
import 'package:mobile/src/core/api/api_exception.dart';
import 'package:mobile/src/core/storage/token_storage.dart';
import 'package:mobile/src/features/auth/data/auth_repository.dart';
import 'package:mobile/src/features/auth/models/account.dart';
import 'package:mobile/src/features/auth/models/login_response.dart';
import 'package:mobile/src/features/auth/models/staff_login_response.dart';
import 'package:mobile/src/features/auth/models/staff_user.dart';
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
import 'package:mobile/src/features/staff/staff_session_controller.dart';
import 'package:mobile/src/providers.dart';

import 'helpers/fakes.dart';

void main() {
  testWidgets(
    'players list shows balances and remains RTL after switching to Arabic',
    (tester) async {
      final playerRepository = FakePlayerRepository(
        players: const [
          PlayerSummary(
            id: 1,
            fullName: 'Ahmed Ali',
            teamName: 'LFC U12',
            playingPosition: 'Forward',
            pointsBalance: 120,
            progress: 'Joined',
            isPlayer: true,
          ),
        ],
      );

      await tester.pumpWidget(
        _testApp(session: _parentState(), playerRepository: playerRepository),
      );
      await tester.pumpAndSettle();

      expect(find.byKey(const Key('player-balance-1')), findsOneWidget);
      expect(find.text('120 pts'), findsOneWidget);

      await tester.tap(find.byKey(const Key('language-toggle')).first);
      await tester.pumpAndSettle();

      expect(find.text('اللاعبون'), findsWidgets);
      expect(
        Directionality.of(tester.element(find.text('Ahmed Ali'))),
        TextDirection.rtl,
      );
    },
  );

  testWidgets('redeem success renders a voucher dialog', (tester) async {
    final redemptionRepository = FakeRedemptionRepository(
      items: const [
        RedemptionItemSummary(
          id: 10,
          name: 'VIP Match Ticket',
          description: 'Premium seat',
          type: 'event',
          pointsCost: 80,
          inStock: true,
        ),
      ],
      voucher: RedemptionVoucher(
        id: 1,
        voucherCode: 'LFC-123',
        pointsSpent: 80,
        status: 'issued',
        itemName: 'VIP Match Ticket',
        itemType: 'event',
        playerName: 'Ahmed Ali',
        createdAt: DateTime(2026, 7, 1),
      ),
    );

    await tester.pumpWidget(
      _testApp(
        session: _parentState(),
        playerRepository: FakePlayerRepository(players: _players),
        redemptionRepository: redemptionRepository,
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Rewards'));
    await tester.pumpAndSettle();
    final redeemSuccessButton = tester.widget<FilledButton>(
      find.byKey(const Key('redeem-item-10')),
    );
    redeemSuccessButton.onPressed!.call();
    await tester.pumpAndSettle();

    expect(find.text('Voucher issued'), findsOneWidget);
    expect(find.textContaining('LFC-123'), findsOneWidget);
  });

  testWidgets('redeem validation error shows localized message', (
    tester,
  ) async {
    final redemptionRepository = FakeRedemptionRepository(
      items: const [
        RedemptionItemSummary(
          id: 10,
          name: 'VIP Match Ticket',
          description: 'Premium seat',
          type: 'event',
          pointsCost: 80,
          inStock: true,
        ),
      ],
      error: ApiException(
        message: 'This item is no longer available.',
        kind: ApiErrorKind.validation,
        statusCode: 422,
      ),
    );

    await tester.pumpWidget(
      _testApp(
        session: _parentState(),
        playerRepository: FakePlayerRepository(players: _players),
        redemptionRepository: redemptionRepository,
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Rewards'));
    await tester.pumpAndSettle();
    final redeemErrorButton = tester.widget<FilledButton>(
      find.byKey(const Key('redeem-item-10')),
    );
    redeemErrorButton.onPressed!.call();
    await tester.pumpAndSettle();

    expect(find.text('This reward is unavailable right now.'), findsOneWidget);
  });

  testWidgets('VVIP client redeem omits player_id', (tester) async {
    final redemptionRepository = FakeRedemptionRepository(
      items: const [
        RedemptionItemSummary(
          id: 11,
          name: 'Club Scarf',
          description: null,
          type: 'merch',
          pointsCost: 50,
          inStock: true,
        ),
      ],
      voucher: RedemptionVoucher(
        id: 2,
        voucherCode: 'LFC-456',
        pointsSpent: 50,
        status: 'issued',
        itemName: 'Club Scarf',
        itemType: 'merch',
        playerName: null,
        createdAt: DateTime(2026, 7, 1),
      ),
    );

    await tester.pumpWidget(
      _testApp(
        session: _vvipState(),
        playerRepository: FakePlayerRepository(),
        redemptionRepository: redemptionRepository,
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Rewards'));
    await tester.pumpAndSettle();
    await tester.tap(find.byKey(const Key('redeem-item-11')));
    await tester.pumpAndSettle();

    expect(redemptionRepository.lastPlayerId, isNull);
  });

  testWidgets('offers list renders VVIP rows', (tester) async {
    final offersRepository = FakeOffersRepository(
      offers: const [
        OfferSummary(
          id: 1,
          title: 'Members Day',
          body: 'Open to all families',
          audience: 'all',
          validFrom: null,
          validUntil: null,
        ),
        OfferSummary(
          id: 2,
          title: 'VVIP Lounge',
          body: 'Private hospitality access',
          audience: 'vvip',
          validFrom: null,
          validUntil: null,
        ),
      ],
    );

    await tester.pumpWidget(
      _testApp(session: _vvipState(), offersRepository: offersRepository),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Offers'));
    await tester.pumpAndSettle();

    expect(find.text('VVIP Lounge'), findsOneWidget);
    expect(find.text('VVIP'), findsAtLeastNWidgets(1));
  });

  testWidgets('QR screen renders and refetches when token expires', (
    tester,
  ) async {
    final scanRepository = FakeScanRepository(
      parentTokens: [
        ScanToken(
          token: 'first-token',
          expiresAt: DateTime.now().add(const Duration(seconds: 1)),
        ),
        ScanToken(
          token: 'second-token',
          expiresAt: DateTime.now().add(const Duration(seconds: 30)),
        ),
      ],
    );

    await tester.pumpWidget(
      _testApp(session: _parentState(), parentScanRepository: scanRepository),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('QR'));
    await tester.pumpAndSettle();

    expect(find.byKey(const Key('parent-qr')), findsOneWidget);
    expect(scanRepository.parentTokenFetchCount, 1);

    final refreshButton = tester.widget<FilledButton>(
      find.ancestor(
        of: find.text('Refresh QR'),
        matching: find.byType(FilledButton),
      ),
    );
    refreshButton.onPressed!.call();
    await tester.pumpAndSettle();

    expect(scanRepository.parentTokenFetchCount, greaterThanOrEqualTo(2));
  });

  testWidgets('staff scan success shows credited players', (tester) async {
    final staffScanRepository = FakeScanRepository(
      fixtures: const [
        FixtureSummary(
          id: 44,
          teamName: 'LFC U12',
          opponent: 'Al Sadd',
          venue: 'Lusail',
          kickoffAt: null,
          scanClosesAt: null,
        ),
      ],
      staffResult: const ScanResult(
        scanId: 9,
        credited: [
          ScanCredit(playerId: 1, playerName: 'Ahmed Ali', points: 25),
        ],
        totalPoints: 25,
      ),
    );

    await tester.pumpWidget(
      _testApp(
        session: const SessionState(status: SessionStatus.unauthenticated),
        staffSession: const StaffSessionState(
          status: StaffSessionStatus.authenticated,
          user: StaffUser(id: 7, name: 'Scanner', email: 'staff@example.com'),
          token: 'staff-token',
        ),
        staffScanRepository: staffScanRepository,
      ),
    );
    await tester.pumpAndSettle();

    await tester.enterText(
      find.byKey(const Key('manual-token-field')),
      'scan-token',
    );
    await tester.tap(find.byKey(const Key('manual-scan-submit')));
    await tester.pumpAndSettle();

    expect(find.byKey(const Key('staff-scan-result')), findsOneWidget);
    expect(find.text('Ahmed Ali: 25'), findsOneWidget);
    expect(find.text('Total points: 25'), findsOneWidget);
  });

  testWidgets(
    'players list refreshes after a redeem invalidates the shared provider',
    (tester) async {
      final playerRepository = FakePlayerRepository(
        playersResponses: const [
          [
            PlayerSummary(
              id: 1,
              fullName: 'Ahmed Ali',
              teamName: 'LFC U12',
              playingPosition: 'Forward',
              pointsBalance: 150,
              progress: 'Joined',
              isPlayer: true,
            ),
          ],
          [
            PlayerSummary(
              id: 1,
              fullName: 'Ahmed Ali',
              teamName: 'LFC U12',
              playingPosition: 'Forward',
              pointsBalance: 140,
              progress: 'Joined',
              isPlayer: true,
            ),
          ],
        ],
      );
      final redemptionRepository = FakeRedemptionRepository(
        items: const [
          RedemptionItemSummary(
            id: 22,
            name: 'LFC Water Bottle',
            description: null,
            type: 'merch',
            pointsCost: 10,
            inStock: true,
          ),
        ],
        voucher: RedemptionVoucher(
          id: 4,
          voucherCode: 'LFC-789',
          pointsSpent: 10,
          status: 'issued',
          itemName: 'LFC Water Bottle',
          itemType: 'merch',
          playerName: 'Ahmed Ali',
          createdAt: DateTime(2026, 7, 1),
        ),
      );

      await tester.pumpWidget(
        _testApp(
          session: _parentState(),
          playerRepository: playerRepository,
          redemptionRepository: redemptionRepository,
        ),
      );
      await tester.pumpAndSettle();

      expect(find.text('150 pts'), findsOneWidget);

      await tester.tap(find.text('Rewards'));
      await tester.pumpAndSettle();
      final redeemButton = tester.widget<FilledButton>(
        find.byKey(const Key('redeem-item-22')),
      );
      redeemButton.onPressed!.call();
      await tester.pumpAndSettle();
      await tester.tap(find.text('Close'));
      await tester.pumpAndSettle();

      await tester.tap(find.text('Players'));
      await tester.pumpAndSettle();

      expect(find.text('140 pts'), findsOneWidget);
    },
  );
}

Widget _testApp({
  required SessionState session,
  StaffSessionState staffSession = const StaffSessionState.hidden(),
  FakePlayerRepository? playerRepository,
  FakeRedemptionRepository? redemptionRepository,
  FakeOffersRepository? offersRepository,
  FakeScanRepository? parentScanRepository,
  FakeScanRepository? staffScanRepository,
}) {
  final authRepository = _FakeAuthRepository(account: session.account);

  return ProviderScope(
    overrides: [
      appConfigProvider.overrideWithValue(
        const AppConfig(apiBaseUrl: 'http://localhost:8000/api/v1'),
      ),
      secureStorageProvider.overrideWithValue(MemorySecureStorage()),
      authRepositoryProvider.overrideWithValue(authRepository),
      sessionControllerProvider.overrideWith(
        () => _FakeSessionController(session, authRepository),
      ),
      staffSessionControllerProvider.overrideWith(
        () => _FakeStaffSessionController(staffSession),
      ),
      if (playerRepository != null)
        playerRepositoryProvider.overrideWithValue(playerRepository),
      if (redemptionRepository != null)
        redemptionRepositoryProvider.overrideWithValue(redemptionRepository),
      if (offersRepository != null)
        offersRepositoryProvider.overrideWithValue(offersRepository),
      if (parentScanRepository != null)
        parentScanRepositoryProvider.overrideWithValue(parentScanRepository),
      if (staffScanRepository != null)
        staffScanRepositoryProvider.overrideWithValue(staffScanRepository),
    ],
    child: const LfcApp(),
  );
}

SessionState _parentState() {
  return const SessionState(
    status: SessionStatus.authenticated,
    account: Account(
      id: 1,
      name: 'Parent Demo',
      email: 'parent@example.com',
      phone: null,
      whatsapp: null,
      isVvip: false,
      accountType: 'parent',
      accountBalance: 0,
    ),
  );
}

SessionState _vvipState() {
  return const SessionState(
    status: SessionStatus.authenticated,
    account: Account(
      id: 2,
      name: 'VVIP Demo',
      email: 'vvip@example.com',
      phone: null,
      whatsapp: null,
      isVvip: true,
      accountType: 'vvip_client',
      accountBalance: 500,
    ),
  );
}

const _players = [
  PlayerSummary(
    id: 1,
    fullName: 'Ahmed Ali',
    teamName: 'LFC U12',
    playingPosition: 'Forward',
    pointsBalance: 120,
    progress: 'Joined',
    isPlayer: true,
  ),
];

class FakePlayerRepository extends PlayerRepository {
  FakePlayerRepository({
    this.players = const [],
    this.playersResponses = const [],
    this.playerTransactions = const [],
    this.accountTransactions = const [],
  }) : super(Dio());

  final List<PlayerSummary> players;
  final List<List<PlayerSummary>> playersResponses;
  final List<PointHistoryEntry> playerTransactions;
  final List<PointHistoryEntry> accountTransactions;
  int fetchPlayersCount = 0;

  @override
  Future<List<PlayerSummary>> fetchPlayers() async {
    if (playersResponses.isEmpty) {
      return players;
    }

    final index = fetchPlayersCount.clamp(0, playersResponses.length - 1);
    fetchPlayersCount += 1;

    return playersResponses[index];
  }

  @override
  Future<List<PointHistoryEntry>> fetchPlayerTransactions(int playerId) async =>
      playerTransactions;

  @override
  Future<List<PointHistoryEntry>> fetchAccountTransactions() async =>
      accountTransactions;
}

class FakeRedemptionRepository extends RedemptionRepository {
  FakeRedemptionRepository({
    this.items = const [],
    this.history = const [],
    this.voucher,
    this.error,
  }) : super(Dio());

  final List<RedemptionItemSummary> items;
  final List<RedemptionHistoryItem> history;
  final RedemptionVoucher? voucher;
  final ApiException? error;
  int? lastPlayerId;

  @override
  Future<List<RedemptionItemSummary>> fetchItems() async => items;

  @override
  Future<List<RedemptionHistoryItem>> fetchHistory() async => history;

  @override
  Future<RedemptionVoucher> redeem({
    required int redemptionItemId,
    int? playerId,
  }) async {
    lastPlayerId = playerId;
    if (error != null) {
      throw error!;
    }
    return voucher!;
  }
}

class FakeOffersRepository extends OffersRepository {
  FakeOffersRepository({this.offers = const []}) : super(Dio());

  final List<OfferSummary> offers;

  @override
  Future<List<OfferSummary>> fetchOffers() async => offers;
}

class FakeScanRepository extends ScanRepository {
  FakeScanRepository({
    this.parentTokens = const [],
    this.fixtures = const [],
    this.staffResult,
  }) : super(Dio());

  final List<ScanToken> parentTokens;
  final List<FixtureSummary> fixtures;
  final ScanResult? staffResult;
  int parentTokenFetchCount = 0;

  @override
  Future<ScanToken> fetchParentToken() async {
    final index = parentTokenFetchCount.clamp(0, parentTokens.length - 1);
    parentTokenFetchCount += 1;
    return parentTokens[index];
  }

  @override
  Future<List<FixtureSummary>> fetchOpenFixtures() async => fixtures;

  @override
  Future<ScanResult> submitScan({
    required int fixtureId,
    required String token,
  }) async {
    return staffResult!;
  }
}

class _FakeAuthRepository extends AuthRepository {
  _FakeAuthRepository({this.account})
    : super(dio: Dio(), tokenStorage: TokenStorage(MemorySecureStorage()));

  final Account? account;

  @override
  Future<LoginResponse> login({
    required String email,
    required String password,
  }) async {
    throw UnimplementedError();
  }

  @override
  Future<Account> getMe() async {
    if (account == null) {
      throw UnimplementedError();
    }

    return account!;
  }

  @override
  Future<StaffLoginResponse> staffLogin({
    required String email,
    required String password,
  }) async {
    throw UnimplementedError();
  }

  @override
  Future<void> logout() async {}

  @override
  Future<String?> readStoredToken() async => null;

  @override
  Future<void> clearToken() async {}
}

class _FakeSessionController extends SessionController {
  _FakeSessionController(this.initialState, this.repository);

  final SessionState initialState;
  final AuthRepository repository;

  @override
  SessionState build() => initialState;
}

class _FakeStaffSessionController extends StaffSessionController {
  _FakeStaffSessionController(this.initialState);

  final StaffSessionState initialState;

  @override
  StaffSessionState build() => initialState;
}
