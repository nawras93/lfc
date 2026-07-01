import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../features/auth/models/account.dart';
import '../../../providers.dart';
import '../models/player_summary.dart';
import 'player_detail_screen.dart';
import 'points_history_screen.dart';

class PlayersScreen extends ConsumerStatefulWidget {
  const PlayersScreen({
    super.key,
    required this.account,
  });

  final Account account;

  @override
  ConsumerState<PlayersScreen> createState() => _PlayersScreenState();
}

class _PlayersScreenState extends ConsumerState<PlayersScreen> {
  late Future<List<PlayerSummary>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<List<PlayerSummary>> _load() => ref.read(playerRepositoryProvider).fetchPlayers();

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return FutureBuilder<List<PlayerSummary>>(
      future: _future,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return Center(child: Text(l10n.loadingText));
        }

        if (snapshot.hasError) {
          return Center(child: Text(snapshot.error.toString()));
        }

        final players = snapshot.data ?? const <PlayerSummary>[];
        if (players.isEmpty) {
          return Center(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    l10n.playersEmptyTitle,
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.titleLarge,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    l10n.playersEmptyBody,
                    textAlign: TextAlign.center,
                  ),
                  if (widget.account.accountType == 'vvip_client') ...[
                    const SizedBox(height: 16),
                    FilledButton.tonal(
                      onPressed: () {
                        Navigator.of(context).push(
                          MaterialPageRoute<void>(
                            builder: (_) => const PointsHistoryScreen(accountHistory: true),
                          ),
                        );
                      },
                      child: Text(l10n.accountPointsHistoryTitle),
                    ),
                  ],
                ],
              ),
            ),
          );
        }

        return RefreshIndicator(
          onRefresh: () async {
            final next = _load();
            setState(() => _future = next);
            await next;
          },
          child: ListView.separated(
            key: const Key('players-list'),
            padding: const EdgeInsets.all(16),
            itemCount: players.length,
            separatorBuilder: (_, _) => const SizedBox(height: 12),
            itemBuilder: (context, index) {
              final player = players[index];

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
