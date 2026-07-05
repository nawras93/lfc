import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:lfc_core/lfc_core.dart';
import 'package:lfc_core/src/features/content/data/content_repository.dart';
import 'package:lfc_core/src/features/content/models/match_summary.dart';
import 'package:lfc_core/src/features/content/models/news_article.dart';
import 'package:lfc_core/src/features/content/models/news_summary.dart';
import 'package:lfc_core/src/features/content/models/standing_row.dart';
import 'package:lfc_core/src/features/session/session_controller.dart';
import 'package:lfc_core/src/providers.dart';

import 'helpers/fakes.dart';
import 'helpers/test_brand.dart';

void main() {
  testWidgets('guest home renders news items from content repository', (
    tester,
  ) async {
    await _pumpSupporterApp(
      tester,
      contentRepository: FakeContentRepository(
        news: [
          NewsSummary(
            id: 1,
            title: 'Cup final announced',
            excerpt: 'Lusail SC hosts the season finale.',
            imageUrl: null,
            publishedAt: DateTime(2026, 7, 5),
          ),
        ],
      ),
    );

    expect(find.text('Cup final announced'), findsOneWidget);
    expect(find.text('Lusail SC hosts the season finale.'), findsOneWidget);
  });

  testWidgets('matches table renders standings rows and highlights own club', (
    tester,
  ) async {
    await _pumpSupporterApp(
      tester,
      contentRepository: FakeContentRepository(
        standings: const [
          StandingRow(
            position: 1,
            clubName: 'Lusail SC',
            played: 3,
            won: 3,
            drawn: 0,
            lost: 0,
            goalsFor: 7,
            goalsAgainst: 1,
            goalDifference: 6,
            points: 9,
            isOwnClub: true,
          ),
          StandingRow(
            position: 2,
            clubName: 'Doha FC',
            played: 3,
            won: 2,
            drawn: 0,
            lost: 1,
            goalsFor: 5,
            goalsAgainst: 3,
            goalDifference: 2,
            points: 6,
            isOwnClub: false,
          ),
        ],
      ),
    );

    await tester.tap(find.text('Matches'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Table'));
    await tester.pumpAndSettle();

    expect(find.text('Lusail SC'), findsOneWidget);
    expect(find.text('Doha FC'), findsOneWidget);
    expect(find.byKey(const Key('own-club-cell')), findsOneWidget);

    final row = tester.widget<Container>(
      find.byKey(const Key('standing-row-1')),
    );
    final decoration = row.decoration! as BoxDecoration;
    expect(decoration.color, isNotNull);
  });

  testWidgets('membership tab shows sign-in gate for guests', (tester) async {
    await _pumpSupporterApp(tester);

    await tester.tap(find.text('Membership'));
    await tester.pumpAndSettle();

    expect(find.text('Sign in to access your membership'), findsOneWidget);
    expect(find.byKey(const Key('membership-sign-in-button')), findsOneWidget);
  });

  testWidgets('supporter app becomes RTL after switching to Arabic', (
    tester,
  ) async {
    await _pumpSupporterApp(tester);

    expect(
      Directionality.of(tester.element(find.text('Home').first)),
      TextDirection.ltr,
    );

    await tester.tap(find.byKey(const Key('language-toggle')).first);
    await tester.pumpAndSettle();

    expect(find.text('الرئيسية'), findsWidgets);
    expect(
      Directionality.of(tester.element(find.text('الرئيسية').first)),
      TextDirection.rtl,
    );
  });
}

Future<void> _pumpSupporterApp(
  WidgetTester tester, {
  SessionState? session,
  FakeContentRepository? contentRepository,
}) async {
  tester.view.physicalSize = const Size(800, 600);
  tester.view.devicePixelRatio = 1;
  addTearDown(() {
    tester.view.resetPhysicalSize();
    tester.view.resetDevicePixelRatio();
  });

  final sessionState =
      session ?? const SessionState(status: SessionStatus.unauthenticated);

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
        contentRepositoryProvider.overrideWithValue(
          contentRepository ?? FakeContentRepository(),
        ),
        sessionControllerProvider.overrideWith(
          () => _FakeSessionController(sessionState),
        ),
      ],
      child: const SupporterApp(),
    ),
  );
  await tester.pumpAndSettle();
}

class FakeContentRepository extends ContentRepository {
  FakeContentRepository({
    this.news = const [],
    this.newsArticle,
    this.fixtures = const [],
    this.results = const [],
    this.standings = const [],
  }) : super(Dio());

  final List<NewsSummary> news;
  final NewsArticle? newsArticle;
  final List<MatchSummary> fixtures;
  final List<MatchSummary> results;
  final List<StandingRow> standings;

  @override
  Future<List<NewsSummary>> fetchNews() async => news;

  @override
  Future<NewsArticle> fetchNewsArticle(int id) async {
    return newsArticle ??
        NewsArticle(
          id: id,
          title: news.isNotEmpty ? news.first.title : 'Article',
          excerpt: news.isNotEmpty ? news.first.excerpt : '',
          body: 'Body',
          imageUrl: null,
          publishedAt: DateTime(2026, 7, 5),
        );
  }

  @override
  Future<List<MatchSummary>> fetchFixtures() async => fixtures;

  @override
  Future<List<MatchSummary>> fetchResults() async => results;

  @override
  Future<List<StandingRow>> fetchStandings() async => standings;
}

class _FakeSessionController extends SessionController {
  _FakeSessionController(this.initialState);

  final SessionState initialState;

  @override
  SessionState build() => initialState;
}
