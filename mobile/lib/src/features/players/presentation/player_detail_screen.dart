import 'package:flutter/material.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../theme/widgets/pills.dart';
import '../models/player_summary.dart';
import 'points_history_screen.dart';
import 'widgets/player_avatar.dart';

class PlayerDetailScreen extends StatelessWidget {
  const PlayerDetailScreen({super.key, required this.player});

  final PlayerSummary player;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: Text(player.fullName)),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  PlayerAvatar(name: player.fullName, size: 72),
                  const SizedBox(height: 14),
                  Text(
                    player.fullName,
                    textAlign: TextAlign.center,
                    style: theme.textTheme.headlineSmall,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${player.teamName ?? l10n.notAvailableValue} · ${player.playingPosition}',
                    textAlign: TextAlign.center,
                    style: theme.textTheme.bodySmall,
                  ),
                  const SizedBox(height: 16),
                  PointsPill(
                    icon: Icons.stars_rounded,
                    label: '${player.pointsBalance} ${l10n.pointsUnit}',
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
              child: Column(
                children: [
                  _DetailRow(
                    label: l10n.teamLabel,
                    value: player.teamName ?? l10n.notAvailableValue,
                  ),
                  const Divider(height: 1),
                  _DetailRow(
                    label: l10n.positionLabel,
                    value: player.playingPosition,
                  ),
                  const Divider(height: 1),
                  _DetailRow(label: l10n.progressLabel, value: player.progress),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          FilledButton.icon(
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute<void>(
                  builder: (_) => PointsHistoryScreen(player: player),
                ),
              );
            },
            icon: const Icon(Icons.receipt_long_outlined),
            label: Text(l10n.pointsHistoryAction),
          ),
        ],
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  const _DetailRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 14),
      child: Row(
        children: [
          Expanded(
            child: Text(
              label,
              style: theme.textTheme.labelMedium?.copyWith(
                letterSpacing: 0.4,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              textAlign: TextAlign.end,
              style: theme.textTheme.titleSmall,
            ),
          ),
        ],
      ),
    );
  }
}
