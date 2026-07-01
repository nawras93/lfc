import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../core/api/api_exception.dart';
import '../../../providers.dart';
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

  bool get _isVvipClient => widget.account.accountType == 'vvip_client';

  @override
  void initState() {
    super.initState();
    _future = _load();
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
      setState(() => _error = l10n.selectPlayerBeforeRedeem);
      return;
    }

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
        setState(() => _error = _friendlyError(l10n, error));
      }
    } finally {
      if (mounted) {
        setState(() => _submitting = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
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
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
                  child: Text(
                    _error!,
                    style: TextStyle(
                      color: Theme.of(context).colorScheme.error,
                    ),
                  ),
                ),
              if (!_isVvipClient)
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
                  child: DropdownButtonFormField<int>(
                    key: const Key('reward-player-select'),
                    initialValue: _selectedPlayerId,
                    decoration: InputDecoration(
                      labelText: l10n.redeemForPlayerLabel,
                    ),
                    items: data.players
                        .map(
                          (player) => DropdownMenuItem<int>(
                            value: player.id,
                            child: Text(player.fullName),
                          ),
                        )
                        .toList(),
                    onChanged: (value) =>
                        setState(() => _selectedPlayerId = value),
                  ),
                ),
              TabBar(
                tabs: [
                  Tab(icon: const Icon(Icons.redeem), text: l10n.catalogTab),
                  Tab(
                    icon: const Icon(Icons.confirmation_number),
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
                        padding: const EdgeInsets.all(16),
                        itemCount: data.items.length,
                        separatorBuilder: (_, _) => const SizedBox(height: 12),
                        itemBuilder: (context, index) {
                          final item = data.items[index];
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
                                          item.name,
                                          style: Theme.of(
                                            context,
                                          ).textTheme.titleLarge,
                                        ),
                                      ),
                                      Chip(
                                        label: Text(
                                          '${item.pointsCost} ${l10n.pointsUnit}',
                                        ),
                                      ),
                                    ],
                                  ),
                                  if (item.description?.isNotEmpty == true) ...[
                                    const SizedBox(height: 8),
                                    Text(item.description!),
                                  ],
                                  const SizedBox(height: 12),
                                  Text(_typeLabel(l10n, item.type)),
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
    final formatter = DateFormat.yMMMd(locale).add_jm();

    if (history.isEmpty) {
      return Center(child: Text(l10n.vouchersEmpty));
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: history.length,
      separatorBuilder: (_, _) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final voucher = history[index];
        return Card(
          child: ListTile(
            title: Text(voucher.itemName),
            subtitle: Text(
              [
                voucher.voucherCode,
                voucher.playerName ?? l10n.accountLabel,
                '${voucher.pointsSpent} ${l10n.pointsUnit}',
                formatter.format(voucher.createdAt.toLocal()),
              ].join(' • '),
            ),
            trailing: Text(voucher.status),
          ),
        );
      },
    );
  }
}

class _VoucherDialog extends StatelessWidget {
  const _VoucherDialog({required this.voucher});

  final RedemptionVoucher voucher;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return AlertDialog(
      title: Text(l10n.voucherDialogTitle),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(voucher.itemName, style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 12),
          Text('${l10n.voucherCodeLabel}: ${voucher.voucherCode}'),
          Text('${l10n.pointsSpentLabel}: ${voucher.pointsSpent}'),
          Text('${l10n.statusLabel}: ${voucher.status}'),
          if (voucher.playerName != null)
            Text('${l10n.playerLabel}: ${voucher.playerName}'),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(),
          child: Text(l10n.closeButton),
        ),
      ],
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
