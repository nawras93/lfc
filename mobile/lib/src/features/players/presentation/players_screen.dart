import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../features/auth/models/account.dart';
import '../../../providers.dart';
import '../../../theme/app_theme.dart';
import '../models/player_summary.dart';
import 'player_detail_screen.dart';
import 'points_history_screen.dart';
import 'widgets/player_avatar.dart';

class PlayersScreen extends ConsumerWidget {
  const PlayersScreen({super.key, required this.account});

  final Account account;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;
    final players = ref.watch(playersProvider);

    return players.when(
      loading: () => const _CenteredLoader(),
      error: (error, _) => _ErrorState(
        message: error.toString(),
        onRetry: () => ref.invalidate(playersProvider),
        retryLabel: l10n.retryButton,
      ),
      data: (items) {
        if (items.isEmpty) {
          return RefreshIndicator(
            onRefresh: () async {
              final next = ref.refresh(playersProvider.future);
              await next;
            },
            child: ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(20, 40, 20, 24),
              children: [
                _EmptyPlayers(
                  account: account,
                  title: l10n.playersEmptyTitle,
                  body: l10n.playersEmptyBody,
                  historyLabel: l10n.accountPointsHistoryTitle,
                ),
              ],
            ),
          );
        }

        return RefreshIndicator(
          onRefresh: () async {
            final next = ref.refresh(playersProvider.future);
            await next;
          },
          child: ListView.separated(
            key: const Key('players-list'),
            padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
            itemCount: items.length,
            separatorBuilder: (_, _) => const SizedBox(height: 12),
            itemBuilder: (context, index) => _PlayerCard(
              player: items[index],
              pointsUnit: l10n.pointsUnit,
            ),
          ),
        );
      },
    );
  }
}

class _PlayerCard extends StatelessWidget {
  const _PlayerCard({required this.player, required this.pointsUnit});

  final PlayerSummary player;
  final String pointsUnit;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final theme = Theme.of(context);

    return Card(
      child: InkWell(
        onTap: () {
          Navigator.of(context).push(
            MaterialPageRoute<void>(
              builder: (_) => PlayerDetailScreen(player: player),
            ),
          );
        },
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              PlayerAvatar(name: player.fullName),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      player.fullName,
                      style: theme.textTheme.titleMedium,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 3),
                    Text(
                      '${player.teamName ?? l10n.notAvailableValue} · ${player.playingPosition}',
                      style: theme.textTheme.bodySmall,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(
                          Icons.trending_up,
                          size: 14,
                          color: theme.colorScheme.onSurfaceVariant,
                        ),
                        const SizedBox(width: 4),
                        Flexible(
                          child: Text(
                            player.progress,
                            style: theme.textTheme.labelMedium,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    '${player.pointsBalance} $pointsUnit',
                    key: Key('player-balance-${player.id}'),
                    style: theme.textTheme.titleLarge?.copyWith(
                      color: context.lfc.gold,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Icon(
                    Icons.chevron_right,
                    size: 20,
                    color: theme.colorScheme.onSurfaceVariant,
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _EmptyPlayers extends StatelessWidget {
  const _EmptyPlayers({
    required this.account,
    required this.title,
    required this.body,
    required this.historyLabel,
  });

  final Account account;
  final String title;
  final String body;
  final String historyLabel;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 76,
          height: 76,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: theme.colorScheme.surfaceContainerHighest,
          ),
          child: Icon(
            Icons.groups_2_outlined,
            size: 36,
            color: theme.colorScheme.onSurfaceVariant,
          ),
        ),
        const SizedBox(height: 18),
        Text(title, textAlign: TextAlign.center, style: theme.textTheme.titleLarge),
        const SizedBox(height: 8),
        Text(
          body,
          textAlign: TextAlign.center,
          style: theme.textTheme.bodyMedium?.copyWith(
            color: theme.colorScheme.onSurfaceVariant,
          ),
        ),
        if (account.accountType == 'vvip_client') ...[
          const SizedBox(height: 20),
          OutlinedButton.icon(
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute<void>(
                  builder: (_) =>
                      const PointsHistoryScreen(accountHistory: true),
                ),
              );
            },
            icon: const Icon(Icons.history),
            label: Text(historyLabel),
          ),
        ],
      ],
    );
  }
}

class _CenteredLoader extends StatelessWidget {
  const _CenteredLoader();

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Padding(
        padding: EdgeInsets.all(32),
        child: CircularProgressIndicator(),
      ),
    );
  }
}

class _ErrorState extends StatelessWidget {
  const _ErrorState({
    required this.message,
    required this.onRetry,
    required this.retryLabel,
  });

  final String message;
  final VoidCallback onRetry;
  final String retryLabel;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return LayoutBuilder(
      builder: (context, constraints) {
        return SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: ConstrainedBox(
            constraints: BoxConstraints(minHeight: constraints.maxHeight),
            child: Center(
              child: Padding(
                padding: const EdgeInsets.all(28),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.cloud_off_outlined,
                      size: 40,
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                    const SizedBox(height: 14),
                    Text(
                      message,
                      textAlign: TextAlign.center,
                      maxLines: 4,
                      overflow: TextOverflow.ellipsis,
                      style: theme.textTheme.bodyMedium?.copyWith(
                        color: theme.colorScheme.onSurfaceVariant,
                      ),
                    ),
                    const SizedBox(height: 18),
                    OutlinedButton.icon(
                      onPressed: onRetry,
                      icon: const Icon(Icons.refresh),
                      label: Text(retryLabel),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}
