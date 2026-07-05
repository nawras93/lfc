import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../core/api/api_exception.dart';
import '../../../providers.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/widgets/qr_pass_card.dart';
import '../../players/models/point_history_entry.dart';

class DiscountWalletScreen extends ConsumerStatefulWidget {
  const DiscountWalletScreen({super.key});

  @override
  ConsumerState<DiscountWalletScreen> createState() =>
      _DiscountWalletScreenState();
}

class _DiscountWalletScreenState extends ConsumerState<DiscountWalletScreen> {
  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final session = ref.watch(sessionControllerProvider);
    final account = session.account;
    final transactionsAsync = ref.watch(accountTransactionsProvider);
    final theme = Theme.of(context);
    final palette = context.lfc;

    final discountPct = account?.discountPercent ?? 0;
    final discountCap = account?.discountCapPercent ?? 0;

    return RefreshIndicator(
      onRefresh: () async {
        await ref.read(sessionControllerProvider.notifier).refreshAccount();
        ref.invalidate(accountTransactionsProvider);
      },
      child: ListView(
        key: const Key('discount-wallet-list'),
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        children: [
          _DiscountCard(
            discountPercent: discountPct,
            discountCapPercent: discountCap,
            l10n: l10n,
            palette: palette,
            theme: theme,
          ),
          const SizedBox(height: 24),
          Text(
            l10n.memberQrTitle,
            style: theme.textTheme.titleMedium,
          ),
          const SizedBox(height: 4),
          SizedBox(
            height: 380,
            child: QrPassCard(
              title: null,
              subtitle: l10n.memberQrSubtitle,
            ),
          ),
          const SizedBox(height: 24),
          Text(
            l10n.discountHistoryTitle,
            style: theme.textTheme.titleMedium,
          ),
          const SizedBox(height: 12),
          transactionsAsync.when(
            data: (transactions) => transactions.isEmpty
                ? Padding(
                    padding: const EdgeInsets.symmetric(vertical: 32),
                    child: Center(
                      child: Text(
                        l10n.discountHistoryEmpty,
                        style: theme.textTheme.bodyMedium?.copyWith(
                          color: theme.colorScheme.onSurfaceVariant,
                        ),
                      ),
                    ),
                  )
                : Column(
                    children: transactions.map(
                      (txn) => _TransactionRow(
                        entry: txn,
                        l10n: l10n,
                        theme: theme,
                      ),
                    ).toList(),
                  ),
            loading: () => const Center(
              child: Padding(
                padding: EdgeInsets.all(32),
                child: CircularProgressIndicator(),
              ),
            ),
            error: (error, _) {
              final msg = error is ApiException
                  ? error.message
                  : error.toString().replaceFirst('Exception: ', '');
              return Center(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Text(
                    msg,
                    textAlign: TextAlign.center,
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: theme.colorScheme.error,
                    ),
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}

class _DiscountCard extends StatelessWidget {
  const _DiscountCard({
    required this.discountPercent,
    required this.discountCapPercent,
    required this.l10n,
    required this.palette,
    required this.theme,
  });

  final double discountPercent;
  final double discountCapPercent;
  final AppLocalizations l10n;
  final LfcPalette palette;
  final ThemeData theme;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(24),
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: palette.heroGradient,
        ),
        boxShadow: [
          BoxShadow(
            color: LfcColors.navy900.withValues(alpha: 0.5),
            blurRadius: 24,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Text(
            l10n.memberDiscountLabel,
            style: TextStyle(
              fontFamily: 'Tajawal',
              fontWeight: FontWeight.w700,
              fontSize: 11.5,
              letterSpacing: 1.6,
              color: palette.goldBright,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            '${discountPercent.toStringAsFixed(1)}%',
            style: TextStyle(
              fontFamily: 'Changa',
              fontWeight: FontWeight.w700,
              fontSize: 52,
              height: 1.0,
              letterSpacing: -1,
              color: palette.onHero,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            l10n.redeemableTowardRegistration,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontFamily: 'Tajawal',
              fontWeight: FontWeight.w500,
              fontSize: 14,
              color: palette.onHeroMuted,
            ),
          ),
          if (discountCapPercent > 0) ...[
            const SizedBox(height: 4),
            Text(
              l10n.discountCapNote(discountCapPercent.toInt()),
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'Tajawal',
                fontWeight: FontWeight.w400,
                fontSize: 12,
                color: palette.onHeroMuted.withValues(alpha: 0.7),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _TransactionRow extends StatelessWidget {
  const _TransactionRow({
    required this.entry,
    required this.l10n,
    required this.theme,
  });

  final PointHistoryEntry entry;
  final AppLocalizations l10n;
  final ThemeData theme;

  @override
  Widget build(BuildContext context) {
    final isCredit = entry.points > 0;
    final pctValue = entry.points / 100;
    final formatted = '${isCredit ? '+' : ''}${pctValue.toStringAsFixed(1)}%';

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: isCredit
                  ? theme.colorScheme.primaryContainer
                  : theme.colorScheme.errorContainer,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              isCredit ? Icons.arrow_upward : Icons.arrow_downward,
              size: 20,
              color: isCredit
                  ? theme.colorScheme.onPrimaryContainer
                  : theme.colorScheme.onErrorContainer,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  entry.source == 'scan'
                      ? l10n.transactionSourceScan
                      : _sourceLabel(entry.source),
                  style: theme.textTheme.titleSmall,
                ),
                const SizedBox(height: 2),
                Text(
                  _formatDate(entry.createdAt),
                  style: theme.textTheme.bodySmall,
                ),
              ],
            ),
          ),
          Text(
            formatted,
            style: TextStyle(
              fontFamily: 'Changa',
              fontWeight: FontWeight.w700,
              fontSize: 16,
              color: isCredit
                  ? theme.colorScheme.primary
                  : theme.colorScheme.error,
            ),
          ),
        ],
      ),
    );
  }

  String _sourceLabel(String? source) {
    return switch (source) {
      'redemption' => l10n.transactionSourceRedemption,
      'expire' => l10n.transactionTypeExpire,
      'adjust' => l10n.transactionTypeAdjust,
      'reverse' => l10n.transactionTypeReverse,
      _ => source ?? '',
    };
  }

  String _formatDate(DateTime date) {
    return '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
  }
}
