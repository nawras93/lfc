import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';
import '../models/player_summary.dart';
import '../models/point_history_entry.dart';

class PointsHistoryScreen extends ConsumerStatefulWidget {
  const PointsHistoryScreen({
    super.key,
    this.player,
    this.accountHistory = false,
  });

  final PlayerSummary? player;
  final bool accountHistory;

  @override
  ConsumerState<PointsHistoryScreen> createState() =>
      _PointsHistoryScreenState();
}

class _PointsHistoryScreenState extends ConsumerState<PointsHistoryScreen> {
  late Future<List<PointHistoryEntry>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<List<PointHistoryEntry>> _load() {
    final repository = ref.read(playerRepositoryProvider);
    if (widget.accountHistory) {
      return repository.fetchAccountTransactions();
    }
    return repository.fetchPlayerTransactions(widget.player!.id);
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final locale = Localizations.localeOf(context).toLanguageTag();
    final formatter = DateFormat.yMMMd(locale).add_jm();
    final title = widget.accountHistory
        ? l10n.accountPointsHistoryTitle
        : l10n.playerPointsHistoryTitle(widget.player!.fullName);

    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: FutureBuilder<List<PointHistoryEntry>>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done) {
            return Center(child: Text(l10n.loadingText));
          }

          if (snapshot.hasError) {
            return Center(child: Text(_errorText(l10n, snapshot.error)));
          }

          final entries = snapshot.data ?? const <PointHistoryEntry>[];
          if (entries.isEmpty) {
            return Center(child: Text(l10n.noTransactionsEmpty));
          }

          return RefreshIndicator(
            onRefresh: () async {
              final next = _load();
              setState(() {
                _future = next;
              });
              await next;
            },
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: entries.length,
              separatorBuilder: (_, _) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final entry = entries[index];
                final positive = entry.points >= 0;

                return Card(
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: positive
                          ? Colors.green.withValues(alpha: 0.14)
                          : Colors.red.withValues(alpha: 0.14),
                      child: Text(
                        positive ? '+' : '-',
                        style: TextStyle(
                          color: positive
                              ? Colors.green.shade700
                              : Colors.red.shade700,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                    title: Text(_transactionTypeLabel(l10n, entry.type)),
                    subtitle: Text(
                      [
                        if (entry.reason?.isNotEmpty == true) entry.reason!,
                        if (entry.source != null)
                          _sourceLabel(l10n, entry.source!),
                        formatter.format(entry.createdAt.toLocal()),
                      ].join(' • '),
                    ),
                    trailing: Text(
                      '${positive ? '+' : ''}${entry.points}',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        color: positive
                            ? Colors.green.shade700
                            : Colors.red.shade700,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }

  String _errorText(AppLocalizations l10n, Object? error) {
    if (error is Exception) {
      return error.toString().replaceFirst('Exception: ', '');
    }
    return l10n.genericError;
  }

  String _sourceLabel(AppLocalizations l10n, String source) {
    return switch (source) {
      'scan' => l10n.transactionSourceScan,
      'redemption' => l10n.transactionSourceRedemption,
      _ => source,
    };
  }

  String _transactionTypeLabel(AppLocalizations l10n, String type) {
    return switch (type) {
      'earn' => l10n.transactionTypeEarn,
      'redeem' => l10n.transactionTypeRedeem,
      'expire' => l10n.transactionTypeExpire,
      'adjust' => l10n.transactionTypeAdjust,
      'reverse' => l10n.transactionTypeReverse,
      _ => type,
    };
  }
}
