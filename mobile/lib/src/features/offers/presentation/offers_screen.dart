import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/widgets/pills.dart';
import '../models/offer_summary.dart';

class OffersScreen extends ConsumerStatefulWidget {
  const OffersScreen({super.key});

  @override
  ConsumerState<OffersScreen> createState() => _OffersScreenState();
}

class _OffersScreenState extends ConsumerState<OffersScreen> {
  late Future<List<OfferSummary>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<List<OfferSummary>> _load() =>
      ref.read(offersRepositoryProvider).fetchOffers();

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
    final locale = Localizations.localeOf(context).toLanguageTag();
    final dateFormat = DateFormat.yMMMd(locale);

    return FutureBuilder<List<OfferSummary>>(
      future: _future,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return Center(child: Text(l10n.loadingText));
        }

        if (snapshot.hasError) {
          return Center(child: Text(snapshot.error.toString()));
        }

        final offers = snapshot.data ?? const <OfferSummary>[];
        if (offers.isEmpty) {
          return Center(
            child: Padding(
              padding: const EdgeInsets.all(28),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.local_offer_outlined,
                    size: 40,
                    color: Theme.of(context).colorScheme.onSurfaceVariant,
                  ),
                  const SizedBox(height: 12),
                  Text(l10n.offersEmpty, textAlign: TextAlign.center),
                ],
              ),
            ),
          );
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
            padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
            itemCount: offers.length,
            separatorBuilder: (_, _) => const SizedBox(height: 12),
            itemBuilder: (context, index) {
              final offer = offers[index];
              final theme = Theme.of(context);
              final isVvip = offer.audience == 'vvip';

              return Card(
                child: IntrinsicHeight(
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Container(
                        width: 5,
                        color: isVvip
                            ? context.lfc.gold
                            : theme.colorScheme.primary,
                      ),
                      Expanded(
                        child: Padding(
                          padding: const EdgeInsets.all(18),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      offer.title,
                                      style: theme.textTheme.titleMedium,
                                    ),
                                  ),
                                  if (isVvip) VvipPill(label: l10n.vvipBadge),
                                ],
                              ),
                              const SizedBox(height: 10),
                              Text(
                                offer.body,
                                style: theme.textTheme.bodyMedium?.copyWith(
                                  color: theme.colorScheme.onSurfaceVariant,
                                ),
                              ),
                              const SizedBox(height: 14),
                              Row(
                                children: [
                                  Icon(
                                    Icons.event_outlined,
                                    size: 14,
                                    color: theme.colorScheme.onSurfaceVariant,
                                  ),
                                  const SizedBox(width: 6),
                                  Expanded(
                                    child: Text(
                                      _validityText(l10n, dateFormat, offer),
                                      style: theme.textTheme.bodySmall,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        );
      },
    );
  }

  String _validityText(
    AppLocalizations l10n,
    DateFormat formatter,
    OfferSummary offer,
  ) {
    final from = offer.validFrom == null
        ? l10n.notAvailableValue
        : formatter.format(offer.validFrom!);
    final until = offer.validUntil == null
        ? l10n.notAvailableValue
        : formatter.format(offer.validUntil!);
    return l10n.offerValidity(from, until);
  }
}
