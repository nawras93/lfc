import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../core/formatting/app_date_format.dart';
import '../../../providers.dart';
import '../../../theme/app_theme.dart';
import '../models/match_summary.dart';
import '../models/standing_row.dart';
import 'content_states.dart';

class MatchesScreen extends StatelessWidget {
  const MatchesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return DefaultTabController(
      length: 3,
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
            child: TabBar(
              tabs: [
                Tab(text: l10n.fixturesTitle),
                Tab(text: l10n.resultsTitle),
                Tab(text: l10n.tableTitle),
              ],
            ),
          ),
          const Expanded(
            child: TabBarView(
              children: [
                _MatchesList(kind: _MatchViewKind.fixtures),
                _MatchesList(kind: _MatchViewKind.results),
                _StandingsView(),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

enum _MatchViewKind { fixtures, results }

class _MatchesList extends ConsumerWidget {
  const _MatchesList({required this.kind});

  final _MatchViewKind kind;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;
    final locale = Localizations.localeOf(context).languageCode;
    final provider = kind == _MatchViewKind.fixtures
        ? fixturesProvider
        : resultsProvider;
    final asyncValue = ref.watch(provider);

    return asyncValue.when(
      loading: () => const ContentLoader(),
      error: (error, _) => ContentErrorState(
        message: error.toString(),
        retryLabel: l10n.retryButton,
        onRetry: () => ref.invalidate(provider),
      ),
      data: (matches) {
        if (matches.isEmpty) {
          return ContentEmptyState(
            icon: Icons.sports_soccer_outlined,
            title: kind == _MatchViewKind.fixtures
                ? l10n.fixturesEmptyState
                : l10n.resultsEmptyState,
          );
        }

        return RefreshIndicator(
          onRefresh: () async => ref.refresh(provider.future),
          child: ListView.separated(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
            itemCount: matches.length,
            separatorBuilder: (_, _) => const SizedBox(height: 12),
            itemBuilder: (context, index) => kind == _MatchViewKind.fixtures
                ? _FixtureCard(match: matches[index], locale: locale)
                : _ResultCard(match: matches[index], locale: locale),
          ),
        );
      },
    );
  }
}

class _FixtureCard extends StatelessWidget {
  const _FixtureCard({required this.match, required this.locale});

  final MatchSummary match;
  final String locale;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final theme = Theme.of(context);

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    match.opponent,
                    style: theme.textTheme.titleLarge,
                  ),
                ),
                _HomeAwayChip(isHome: match.isHome),
              ],
            ),
            const SizedBox(height: 6),
            Text(match.competition, style: theme.textTheme.bodyMedium),
            const SizedBox(height: 8),
            Text(
              AppDateFormat.dateTime(locale).format(match.kickoffAt),
              style: theme.textTheme.labelLarge?.copyWith(
                color: theme.colorScheme.onSurfaceVariant,
              ),
            ),
            if (match.venue?.isNotEmpty == true) ...[
              const SizedBox(height: 4),
              Text(
                '${l10n.venueLabel}: ${match.venue!}',
                style: theme.textTheme.bodySmall,
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _ResultCard extends StatelessWidget {
  const _ResultCard({required this.match, required this.locale});

  final MatchSummary match;
  final String locale;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final score = '${match.ourScore ?? 0}-${match.opponentScore ?? 0}';

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Lusail $score ${match.opponent}',
              style: theme.textTheme.titleLarge,
            ),
            const SizedBox(height: 6),
            Text(match.competition, style: theme.textTheme.bodyMedium),
            const SizedBox(height: 8),
            Text(
              AppDateFormat.date(locale).format(match.kickoffAt),
              style: theme.textTheme.labelLarge?.copyWith(
                color: theme.colorScheme.onSurfaceVariant,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _HomeAwayChip extends StatelessWidget {
  const _HomeAwayChip({required this.isHome});

  final bool isHome;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final scheme = Theme.of(context).colorScheme;

    return DecoratedBox(
      decoration: BoxDecoration(
        color: scheme.primary.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: scheme.primary.withValues(alpha: 0.18)),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        child: Text(
          isHome ? l10n.homeMatchChip : l10n.awayMatchChip,
          style: Theme.of(
            context,
          ).textTheme.labelMedium?.copyWith(color: scheme.primary),
        ),
      ),
    );
  }
}

class _StandingsView extends ConsumerWidget {
  const _StandingsView();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;
    final standings = ref.watch(standingsProvider);

    return standings.when(
      loading: () => const ContentLoader(),
      error: (error, _) => ContentErrorState(
        message: error.toString(),
        retryLabel: l10n.retryButton,
        onRetry: () => ref.invalidate(standingsProvider),
      ),
      data: (rows) {
        if (rows.isEmpty) {
          return ContentEmptyState(
            icon: Icons.table_chart_outlined,
            title: l10n.tableEmptyState,
          );
        }

        return RefreshIndicator(
          onRefresh: () async => ref.refresh(standingsProvider.future),
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
            children: [
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: ConstrainedBox(
                  constraints: const BoxConstraints(minWidth: 720),
                  child: _StandingsTable(rows: rows),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class _StandingsTable extends StatelessWidget {
  const _StandingsTable({required this.rows});

  final List<StandingRow> rows;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final scheme = Theme.of(context).colorScheme;

    return ClipRRect(
      borderRadius: BorderRadius.circular(18),
      child: Column(
        children: [
          _StandingsHeader(
            labels: [
              l10n.positionColumnShort,
              l10n.clubColumnLabel,
              l10n.playedColumnShort,
              l10n.wonColumnShort,
              l10n.drawnColumnShort,
              l10n.lostColumnShort,
              l10n.goalDifferenceColumnShort,
              l10n.pointsColumnShort,
            ],
          ),
          for (final row in rows)
            Container(
              key: Key('standing-row-${row.position}'),
              decoration: BoxDecoration(
                color: row.isOwnClub
                    ? context.lfc.gold.withValues(alpha: 0.16)
                    : scheme.surface,
                border: Border(
                  bottom: BorderSide(color: scheme.outlineVariant),
                ),
              ),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              child: Row(
                children: [
                  _TableCell('${row.position}', width: 48),
                  _TableCell(
                    row.clubName,
                    width: 220,
                    isOwnClub: row.isOwnClub,
                    alignStart: true,
                  ),
                  _TableCell('${row.played}', width: 48),
                  _TableCell('${row.won}', width: 48),
                  _TableCell('${row.drawn}', width: 48),
                  _TableCell('${row.lost}', width: 48),
                  _TableCell('${row.goalDifference}', width: 56),
                  _TableCell(
                    '${row.points}',
                    width: 56,
                    isOwnClub: row.isOwnClub,
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }
}

class _StandingsHeader extends StatelessWidget {
  const _StandingsHeader({required this.labels});

  final List<String> labels;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;

    return Container(
      color: scheme.primary,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      child: Row(
        children: [
          _TableCell(labels[0], width: 48, header: true),
          _TableCell(labels[1], width: 220, header: true, alignStart: true),
          _TableCell(labels[2], width: 48, header: true),
          _TableCell(labels[3], width: 48, header: true),
          _TableCell(labels[4], width: 48, header: true),
          _TableCell(labels[5], width: 48, header: true),
          _TableCell(labels[6], width: 56, header: true),
          _TableCell(labels[7], width: 56, header: true),
        ],
      ),
    );
  }
}

class _TableCell extends StatelessWidget {
  const _TableCell(
    this.text, {
    required this.width,
    this.header = false,
    this.isOwnClub = false,
    this.alignStart = false,
  });

  final String text;
  final double width;
  final bool header;
  final bool isOwnClub;
  final bool alignStart;

  @override
  Widget build(BuildContext context) {
    final style = header
        ? Theme.of(context).textTheme.labelLarge?.copyWith(
            color: Theme.of(context).colorScheme.onPrimary,
            fontWeight: FontWeight.w700,
          )
        : Theme.of(context).textTheme.bodyMedium?.copyWith(
            fontWeight: isOwnClub ? FontWeight.w700 : FontWeight.w500,
          );

    return SizedBox(
      width: width,
      child: Text(
        text,
        key: isOwnClub && !header && width == 220
            ? const Key('own-club-cell')
            : null,
        textAlign: alignStart ? TextAlign.start : TextAlign.center,
        overflow: TextOverflow.ellipsis,
        style: style,
      ),
    );
  }
}
