import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../features/auth/models/account.dart';
import '../../../providers.dart';
import 'player_detail_screen.dart';
import 'points_history_screen.dart';

class PlayersScreen extends ConsumerWidget {
  const PlayersScreen({super.key, required this.account});

  final Account account;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;
    final players = ref.watch(playersProvider);

    return players.when(
      loading: () => Center(child: Text(l10n.loadingText)),
      error: (error, _) => Center(child: Text(error.toString())),
      data: (items) {
        if (items.isEmpty) {
          return RefreshIndicator(
            onRefresh: () async {
              final next = ref.refresh(playersProvider.future);
              await next;
            },
            child: ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(24),
              children: [
                Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      l10n.playersEmptyTitle,
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.titleLarge,
                    ),
                    const SizedBox(height: 8),
                    Text(l10n.playersEmptyBody, textAlign: TextAlign.center),
                    if (account.accountType == 'vvip_client') ...[
                      const SizedBox(height: 16),
                      FilledButton.tonal(
                        onPressed: () {
                          Navigator.of(context).push(
                            MaterialPageRoute<void>(
                              builder: (_) => const PointsHistoryScreen(
                                accountHistory: true,
                              ),
                            ),
                          );
                        },
                        child: Text(l10n.accountPointsHistoryTitle),
                      ),
                    ],
                  ],
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
            padding: const EdgeInsets.all(16),
            itemCount: items.length,
            separatorBuilder: (_, _) => const SizedBox(height: 12),
            itemBuilder: (context, index) {
              final player = items[index];

              return Card(
                child: ListTile(
                  title: Text(player.fullName),
                  subtitle: Text(
                    '${player.teamName ?? l10n.notAvailableValue} • ${player.playingPosition}',
                  ),
                  trailing: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        '${player.pointsBalance} ${l10n.pointsUnit}',
                        key: Key('player-balance-${player.id}'),
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      Text(player.progress),
                    ],
                  ),
                  onTap: () {
                    Navigator.of(context).push(
                      MaterialPageRoute<void>(
                        builder: (_) => PlayerDetailScreen(player: player),
                      ),
                    );
                  },
                ),
              );
            },
          ),
        );
      },
    );
  }
}
