import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../core/api/api_exception.dart';
import '../../../core/formatting/app_date_format.dart';
import '../../../providers.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/widgets/pills.dart';
import '../../auth/models/account.dart';
import '../../players/models/player_summary.dart';
import '../models/redemption_history_item.dart';
import '../models/redemption_item_summary.dart';
import '../models/redemption_voucher.dart';

class RewardsScreen extends ConsumerStatefulWidget {
  const RewardsScreen({super.key, required this.account});

  final Account account;

  @override
  ConsumerState<RewardsScreen> createState() => _RewardsScreenState();
}

class _RewardsScreenState extends ConsumerState<RewardsScreen> {
  late Future<_RewardsData> _future;
  int? _selectedPlayerId;
  bool _submitting = false;
  String? _error;
  Timer? _errorTimer;

  bool get _isVvipClient => widget.account.accountType == 'vvip_client';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  @override
  void dispose() {
    _errorTimer?.cancel();
    super.dispose();
  }

  /// Shows a transient error that clears itself after a few seconds. Passing
  /// null dismisses any current error immediately.
  void _setError(String? message) {
    _errorTimer?.cancel();
    setState(() => _error = message);
    if (message != null) {
      _errorTimer = Timer(const Duration(seconds: 5), () {
        if (mounted) {
          setState(() => _error = null);
        }
      });
    }
  }

  Future<_RewardsData> _load() async {
    final itemsFuture = ref.read(redemptionRepositoryProvider).fetchItems();
    final historyFuture = ref.read(redemptionRepositoryProvider).fetchHistory();
    final playersFuture = _isVvipClient
        ? Future.value(const <PlayerSummary>[])
        : ref.read(playersProvider.future);

    final results = await Future.wait<dynamic>([
      itemsFuture,
      historyFuture,
      playersFuture,
    ]);

    final data = _RewardsData(
      items: results[0] as List<RedemptionItemSummary>,
      history: results[1] as List<RedemptionHistoryItem>,
      players: results[2] as List<PlayerSummary>,
    );

    if (_selectedPlayerId == null && data.players.isNotEmpty) {
      _selectedPlayerId = data.players.first.id;
    }

    return data;
  }

  Future<void> _refresh() async {
    final next = _load();
    setState(() {
      _future = next;
    });
    await next;
  }

  Future<void> _redeem(RedemptionItemSummary item) async {
    final l10n = AppLocalizations.of(context)!;

    if (!_isVvipClient && _selectedPlayerId == null) {
      _setError(l10n.selectPlayerBeforeRedeem);
      return;
    }

    _errorTimer?.cancel();
    setState(() {
      _submitting = true;
      _error = null;
    });

    try {
      final voucher = await ref
          .read(redemptionRepositoryProvider)
          .redeem(
            redemptionItemId: item.id,
            playerId: _isVvipClient ? null : _selectedPlayerId,
          );

      if (!mounted) {
        return;
      }

      await showDialog<void>(
        context: context,
        builder: (context) => _VoucherDialog(voucher: voucher),
      );
      ref.invalidate(playersProvider);
      await ref.read(sessionControllerProvider.notifier).refreshAccount();
      await _refresh();
    } on ApiException catch (error) {
      if (mounted) {
        _setError(_friendlyError(l10n, error));
      }
    } finally {
      if (mounted) {
        setState(() => _submitting = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    ref.listen<Locale>(localeControllerProvider, (previous, next) {
      if (previous?.languageCode != next.languageCode) {
        final nextFuture = _load();
        setState(() {
          _future = nextFuture;
        });
      }
    });

    final l10n = AppLocalizations.of(context)!;

    return FutureBuilder<_RewardsData>(
      future: _future,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return Center(child: Text(l10n.loadingText));
        }

        if (snapshot.hasError) {
          return Center(child: Text(snapshot.error.toString()));
        }

        final data = snapshot.data ?? const _RewardsData();

        return DefaultTabController(
          length: 2,
          child: Column(
            children: [
              if (_error != null)
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 8, 20, 0),
                  child: _InlineError(
                    message: _error!,
                    onDismiss: () => _setError(null),
                  ),
                ),
              if (!_isVvipClient)
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
                  child: DropdownMenu<int>(
                    key: const Key('reward-player-select'),
                    initialSelection: _selectedPlayerId,
                    expandedInsets: EdgeInsets.zero,
                    requestFocusOnTap: false,
                    enableSearch: false,
                    menuHeight: 320,
                    label: Text(l10n.redeemForPlayerLabel),
                    leadingIcon: const Icon(Icons.person_outline),
                    onSelected: (value) {
                      _setError(null);
                      setState(() => _selectedPlayerId = value);
                    },
                    dropdownMenuEntries: data.players
                        .map(
                          (player) => DropdownMenuEntry<int>(
                            value: player.id,
                            label: player.fullName,
                          ),
                        )
                        .toList(),
                  ),
                ),
              TabBar(
                tabs: [
                  Tab(icon: const Icon(Icons.redeem), text: l10n.catalogTab),
                  Tab(
                    icon: const Icon(Icons.confirmation_number_outlined),
                    text: l10n.vouchersTab,
                  ),
                ],
              ),
              Expanded(
                child: TabBarView(
                  children: [
                    RefreshIndicator(
                      onRefresh: _refresh,
                      child: ListView.separated(
                        padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
                        itemCount: data.items.length,
                        separatorBuilder: (_, _) => const SizedBox(height: 12),
                        itemBuilder: (context, index) {
                          final item = data.items[index];
                          final theme = Theme.of(context);
                          return Card(
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      _RewardTypeIcon(type: item.type),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              item.name,
                                              style:
                                                  theme.textTheme.titleMedium,
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              _typeLabel(l10n, item.type),
                                              style:
                                                  theme.textTheme.labelMedium,
                                            ),
                                          ],
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      PointsPill(
                                        label:
                                            '${item.pointsCost} ${l10n.pointsUnit}',
                                      ),
                                    ],
                                  ),
                                  if (item.description?.isNotEmpty == true) ...[
                                    const SizedBox(height: 12),
                                    Text(
                                      item.description!,
                                      style: theme.textTheme.bodyMedium
                                          ?.copyWith(
                                            color: theme
                                                .colorScheme
                                                .onSurfaceVariant,
                                          ),
                                    ),
                                  ],
                                  const SizedBox(height: 16),
                                  FilledButton(
                                    key: Key('redeem-item-${item.id}'),
                                    onPressed: !item.inStock || _submitting
                                        ? null
                                        : () => _redeem(item),
                                    child: Text(
                                      item.inStock
                                          ? l10n.redeemButton
                                          : l10n.outOfStockLabel,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                    _VoucherHistoryList(history: data.history),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  String _friendlyError(AppLocalizations l10n, ApiException error) {
    final message = error.message.toLowerCase();

    if (error.statusCode == 403 && message.contains('linked')) {
      return l10n.redeemLinkedPlayerError;
    }

    if (error.statusCode == 422 && message.contains('vvip client account')) {
      return l10n.redeemVvipOnlyError;
    }

    if (error.statusCode == 422 && message.contains('insufficient')) {
      return l10n.redeemInsufficientError;
    }

    if (error.statusCode == 422) {
      return l10n.redeemUnavailableError;
    }

    return error.message.isEmpty ? l10n.genericError : error.message;
  }

  String _typeLabel(AppLocalizations l10n, String type) {
    return switch (type) {
      'fee_discount' => l10n.redemptionTypeFeeDiscount,
      'event' => l10n.redemptionTypeEvent,
      'merch' => l10n.redemptionTypeMerch,
      _ => type,
    };
  }
}

class _VoucherHistoryList extends StatelessWidget {
  const _VoucherHistoryList({required this.history});

  final List<RedemptionHistoryItem> history;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final locale = Localizations.localeOf(context).toLanguageTag();
    final formatter = AppDateFormat.dateTime(locale);

    if (history.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(28),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.confirmation_number_outlined,
                size: 40,
                color: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
              const SizedBox(height: 12),
              Text(l10n.vouchersEmpty, textAlign: TextAlign.center),
            ],
          ),
        ),
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
      itemCount: history.length,
      separatorBuilder: (_, _) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final voucher = history[index];
        final theme = Theme.of(context);
        return Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: context.lfc.gold.withValues(alpha: 0.14),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    Icons.confirmation_number_outlined,
                    color: context.lfc.gold,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(voucher.itemName, style: theme.textTheme.titleSmall),
                      const SizedBox(height: 3),
                      Text(
                        [
                          voucher.voucherCode,
                          voucher.playerName ?? l10n.accountLabel,
                          '${voucher.pointsSpent} ${l10n.pointsUnit}',
                          formatter.format(voucher.createdAt.toLocal()),
                        ].join(' · '),
                        style: theme.textTheme.bodySmall,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                _StatusChip(status: voucher.status),
              ],
            ),
          ),
        );
      },
    );
  }
}

class _RewardTypeIcon extends StatelessWidget {
  const _RewardTypeIcon({required this.type});

  final String type;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final icon = switch (type) {
      'fee_discount' => Icons.local_atm_outlined,
      'event' => Icons.stadium_outlined,
      'merch' => Icons.checkroom_outlined,
      _ => Icons.redeem,
    };
    return Container(
      width: 44,
      height: 44,
      decoration: BoxDecoration(
        color: scheme.primary.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Icon(icon, color: scheme.primary, size: 22),
    );
  }
}

class _StatusChip extends StatelessWidget {
  const _StatusChip({required this.status});

  final String status;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: scheme.surfaceContainerHighest,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        _voucherStatusLabel(AppLocalizations.of(context)!, status),
        style: Theme.of(context).textTheme.labelSmall,
      ),
    );
  }
}

class _InlineError extends StatelessWidget {
  const _InlineError({required this.message, this.onDismiss});

  final String message;
  final VoidCallback? onDismiss;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Container(
      padding: const EdgeInsets.fromLTRB(14, 12, 6, 12),
      decoration: BoxDecoration(
        color: scheme.errorContainer,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Icon(Icons.error_outline, size: 18, color: scheme.onErrorContainer),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: TextStyle(color: scheme.onErrorContainer),
            ),
          ),
          if (onDismiss != null)
            IconButton(
              onPressed: onDismiss,
              icon: const Icon(Icons.close, size: 18),
              color: scheme.onErrorContainer,
              visualDensity: VisualDensity.compact,
              tooltip: MaterialLocalizations.of(context).closeButtonLabel,
            ),
        ],
      ),
    );
  }
}

class _VoucherDialog extends StatelessWidget {
  const _VoucherDialog({required this.voucher});

  final RedemptionVoucher voucher;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    final theme = Theme.of(context);
    final palette = context.lfc;

    return AlertDialog(
      titlePadding: EdgeInsets.zero,
      title: Container(
        padding: const EdgeInsets.fromLTRB(24, 22, 24, 20),
        decoration: BoxDecoration(
          gradient: LinearGradient(colors: palette.heroGradient),
          borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(Icons.check_circle, color: palette.goldBright, size: 22),
                const SizedBox(width: 8),
                Text(
                  l10n.voucherDialogTitle,
                  style: theme.textTheme.titleMedium?.copyWith(
                    color: palette.onHero,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              voucher.itemName,
              style: theme.textTheme.headlineSmall?.copyWith(
                color: palette.onHero,
              ),
            ),
          ],
        ),
      ),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          _VoucherRow(label: l10n.voucherCodeLabel, value: voucher.voucherCode),
          _VoucherRow(
            label: l10n.pointsSpentLabel,
            value: '${voucher.pointsSpent} ${l10n.pointsUnit}',
          ),
          _VoucherRow(
            label: l10n.statusLabel,
            value: _voucherStatusLabel(l10n, voucher.status),
          ),
          if (voucher.playerName != null)
            _VoucherRow(label: l10n.playerLabel, value: voucher.playerName!),
        ],
      ),
      actions: [
        FilledButton(
          onPressed: () => Navigator.of(context).pop(),
          child: Text(l10n.closeButton),
        ),
      ],
    );
  }
}

class _VoucherRow extends StatelessWidget {
  const _VoucherRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(child: Text(label, style: theme.textTheme.labelMedium)),
          const SizedBox(width: 12),
          Flexible(
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

class _RewardsData {
  const _RewardsData({
    this.items = const <RedemptionItemSummary>[],
    this.history = const <RedemptionHistoryItem>[],
    this.players = const <PlayerSummary>[],
  });

  final List<RedemptionItemSummary> items;
  final List<RedemptionHistoryItem> history;
  final List<PlayerSummary> players;
}

String _voucherStatusLabel(AppLocalizations l10n, String status) {
  return switch (status) {
    'issued' => l10n.voucherStatusIssued,
    'fulfilled' => l10n.voucherStatusFulfilled,
    'cancelled' => l10n.voucherStatusCancelled,
    _ => status,
  };
}
