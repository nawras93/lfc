import 'package:flutter/material.dart';

import '../../../../l10n/app_localizations.dart';
import '../models/player_summary.dart';
import 'points_history_screen.dart';

class PlayerDetailScreen extends StatelessWidget {
  const PlayerDetailScreen({
    super.key,
    required this.player,
  });

  final PlayerSummary player;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      appBar: AppBar(title: Text(player.fullName)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(player.fullName, style: Theme.of(context).textTheme.headlineSmall),
                  const SizedBox(height: 16),
                  _DetailRow(label: l10n.teamLabel, value: player.teamName ?? l10n.notAvailableValue),
                  _DetailRow(label: l10n.positionLabel, value: player.playingPosition),
                  _DetailRow(label: l10n.progressLabel, value: player.progress),
                  _DetailRow(label: l10n.balanceLabel, value: player.pointsBalance.toString()),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          FilledButton.tonal(
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute<void>(
                  builder: (_) => PointsHistoryScreen(player: player),
                ),
              );
            },
            child: Text(l10n.pointsHistoryAction),
          ),
        ],
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  const _DetailRow({
    required this.label,
    required this.value,
  });

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Expanded(
            child: Text(
              label,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    fontWeight: FontWeight.w700,
                  ),
            ),
          ),
          Expanded(child: Text(value, textAlign: TextAlign.end)),
        ],
      ),
    );
  }
}
