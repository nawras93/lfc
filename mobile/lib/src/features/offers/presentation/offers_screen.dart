import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';
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

  Future<List<OfferSummary>> _load() => ref.read(offersRepositoryProvider).fetchOffers();

  @override
  Widget build(BuildContext context) {
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
          return Center(child: Text(l10n.offersEmpty));
        }

        return RefreshIndicator(
          onRefresh: () async {
            final next = _load();
            setState(() => _future = next);
            await next;
          },
          child: ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: offers.length,
            separatorBuilder: (_, _) => const SizedBox(height: 12),
            itemBuilder: (context, index) {
              final offer = offers[index];

              return Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              offer.title,
                              style: Theme.of(context).textTheme.titleLarge,
                            ),
                          ),
                          if (offer.audience == 'vvip')
                            Chip(label: Text(l10n.vvipBadge)),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(offer.body),
                      const SizedBox(height: 16),
                      Text(
                        _validityText(l10n, dateFormat, offer),
                        style: Theme.of(context).textTheme.bodySmall,
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
    final from = offer.validFrom == null ? l10n.notAvailableValue : formatter.format(offer.validFrom!);
    final until =
        offer.validUntil == null ? l10n.notAvailableValue : formatter.format(offer.validUntil!);
    return l10n.offerValidity(from, until);
  }
}
