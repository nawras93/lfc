import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../core/api/api_exception.dart';
import '../../../core/formatting/app_date_format.dart';
import '../../../providers.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/widgets/brand_mark.dart';
import '../../../theme/widgets/pills.dart';
import '../../content/presentation/content_states.dart';
import '../../offers/models/offer_summary.dart';
import '../models/membership_benefit.dart';
import '../models/membership_card.dart';
import 'membership_helpers.dart';

class VvipCardScreen extends ConsumerWidget {
  const VvipCardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final membershipAsync = ref.watch(membershipProvider);
    final offersAsync = ref.watch(offersProvider);
    final l10n = AppLocalizations.of(context)!;

    return RefreshIndicator(
      onRefresh: () async {
        await Future.wait([
          ref.refresh(membershipProvider.future),
          ref.refresh(offersProvider.future),
        ]);
      },
      child: membershipAsync.when(
        data: (membership) =>
            _Content(membership: membership, offersAsync: offersAsync),
        loading: () => ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(vertical: 120),
          children: const [ContentLoader()],
        ),
        error: (error, _) => ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          children: [
            SizedBox(
              height: MediaQuery.sizeOf(context).height * 0.7,
              child: ContentErrorState(
                message: _errorMessage(error),
                retryLabel: l10n.retryButton,
                onRetry: () {
                  ref.invalidate(membershipProvider);
                  ref.invalidate(offersProvider);
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _errorMessage(Object error) {
    return error is ApiException
        ? error.message
        : error.toString().replaceFirst('Exception: ', '');
  }
}

class _Content extends StatelessWidget {
  const _Content({required this.membership, required this.offersAsync});

  final MembershipCard? membership;
  final AsyncValue<List<OfferSummary>> offersAsync;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final theme = Theme.of(context);

    return ListView(
      key: const Key('vvip-card-list'),
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      children: [
        if (membership == null)
          _UnavailableCard(message: l10n.membershipUnavailable)
        else
          _MembershipHeroCard(card: membership!),
        const SizedBox(height: 24),
        _SectionTitle(title: l10n.yourBenefitsTitle),
        const SizedBox(height: 12),
        if (membership == null || membership!.benefits.isEmpty)
          _EmptySection(
            icon: Icons.star_outline,
            message: l10n.membershipBenefitsEmpty,
          )
        else
          Column(
            children: [
              for (final benefit in membership!.benefits)
                _BenefitCard(benefit: benefit),
            ],
          ),
        const SizedBox(height: 24),
        _SectionTitle(title: l10n.vvipOffersTitle),
        const SizedBox(height: 12),
        offersAsync.when(
          data: (offers) => offers.isEmpty
              ? _EmptySection(
                  icon: Icons.local_offer_outlined,
                  message: l10n.offersEmpty,
                )
              : Column(
                  children: [
                    for (final offer in offers) _OfferCard(offer: offer),
                  ],
                ),
          loading: () => const Padding(
            padding: EdgeInsets.symmetric(vertical: 20),
            child: Center(child: CircularProgressIndicator()),
          ),
          error: (error, _) => Padding(
            padding: const EdgeInsets.symmetric(vertical: 16),
            child: Text(
              error is ApiException
                  ? error.message
                  : error.toString().replaceFirst('Exception: ', ''),
              textAlign: TextAlign.center,
              style: theme.textTheme.bodyMedium?.copyWith(
                color: theme.colorScheme.error,
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _MembershipHeroCard extends StatelessWidget {
  const _MembershipHeroCard({required this.card});

  final MembershipCard card;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final locale = Localizations.localeOf(context).toLanguageTag();
    final palette = context.lfc;
    final theme = Theme.of(context);
    final accent = membershipAccentColor(card.tier.accentColor, palette.gold);
    final gradient = membershipHeroGradient(context, accent);
    final validUntil = card.validUntil == null
        ? l10n.notAvailableValue
        : AppDateFormat.date(locale).format(card.validUntil!);
    final memberNumber = card.memberNumber ?? l10n.notAvailableValue;

    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(28),
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: gradient,
        ),
        boxShadow: [
          BoxShadow(
            color: LfcColors.navy900.withValues(alpha: 0.42),
            blurRadius: 28,
            offset: const Offset(0, 14),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const BrandMark(onHero: true),
          const SizedBox(height: 22),
          Wrap(
            spacing: 12,
            runSpacing: 8,
            crossAxisAlignment: WrapCrossAlignment.center,
            children: [
              Text(
                card.tier.name,
                style: TextStyle(
                  fontFamily: 'Changa',
                  fontWeight: FontWeight.w700,
                  fontSize: 30,
                  height: 1.0,
                  color: palette.onHero,
                ),
              ),
              VvipPill(label: l10n.vvipBadge),
            ],
          ),
          const SizedBox(height: 18),
          Text(
            l10n.membershipCardLabel,
            style: TextStyle(
              fontFamily: 'Tajawal',
              fontWeight: FontWeight.w700,
              fontSize: 12,
              letterSpacing: 1.2,
              color: accent,
            ),
          ),
          const SizedBox(height: 14),
          LayoutBuilder(
            builder: (context, constraints) {
              final isWide = constraints.maxWidth >= 520;
              final info = _CardInfo(
                memberNumber: memberNumber,
                validUntil: validUntil,
              );
              final qr = _IdentityQr(memberNumber: memberNumber);
              if (isWide) {
                return Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(child: info),
                    const SizedBox(width: 20),
                    qr,
                  ],
                );
              }

              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  info,
                  const SizedBox(height: 20),
                  Align(alignment: Alignment.center, child: qr),
                ],
              );
            },
          ),
          const SizedBox(height: 16),
          Text(
            l10n.validUntilLabel(validUntil),
            style: theme.textTheme.bodyMedium?.copyWith(
              color: palette.onHeroMuted,
            ),
          ),
        ],
      ),
    );
  }
}

class _CardInfo extends StatelessWidget {
  const _CardInfo({required this.memberNumber, required this.validUntil});

  final String memberNumber;
  final String validUntil;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final palette = context.lfc;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          l10n.memberNumberLabel,
          style: TextStyle(
            fontFamily: 'Tajawal',
            fontWeight: FontWeight.w600,
            fontSize: 13,
            color: palette.onHeroMuted,
          ),
        ),
        const SizedBox(height: 6),
        Text(
          memberNumber,
          style: TextStyle(
            fontFamily: 'Changa',
            fontWeight: FontWeight.w700,
            fontSize: 22,
            color: palette.onHero,
          ),
        ),
        const SizedBox(height: 18),
        Text(
          l10n.validUntilLabel(validUntil),
          style: TextStyle(
            fontFamily: 'Tajawal',
            fontWeight: FontWeight.w500,
            fontSize: 14,
            color: palette.onHeroMuted,
          ),
        ),
      ],
    );
  }
}

class _IdentityQr extends StatelessWidget {
  const _IdentityQr({required this.memberNumber});

  final String memberNumber;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(
          color: context.lfc.gold.withValues(alpha: 0.65),
          width: 1.5,
        ),
      ),
      child: QrImageView(
        key: const Key('vvip-qr'),
        data: memberNumber,
        size: 144,
        backgroundColor: Colors.white,
        eyeStyle: const QrEyeStyle(
          eyeShape: QrEyeShape.square,
          color: LfcColors.navy700,
        ),
        dataModuleStyle: const QrDataModuleStyle(
          dataModuleShape: QrDataModuleShape.square,
          color: LfcColors.navy800,
        ),
      ),
    );
  }
}

class _BenefitCard extends StatelessWidget {
  const _BenefitCard({required this.benefit});

  final MembershipBenefit benefit;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: context.lfc.gold.withValues(alpha: 0.14),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(
                membershipBenefitIcon(benefit.icon),
                color: context.lfc.gold,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(benefit.title ?? '', style: theme.textTheme.titleSmall),
                  if ((benefit.description ?? '').isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      benefit.description!,
                      style: theme.textTheme.bodyMedium?.copyWith(
                        color: theme.colorScheme.onSurfaceVariant,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _OfferCard extends StatelessWidget {
  const _OfferCard({required this.offer});

  final OfferSummary offer;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final locale = Localizations.localeOf(context).toLanguageTag();
    final theme = Theme.of(context);
    final isVvip = offer.audience == 'vvip';
    final from = offer.validFrom == null
        ? l10n.notAvailableValue
        : AppDateFormat.date(locale).format(offer.validFrom!);
    final until = offer.validUntil == null
        ? l10n.notAvailableValue
        : AppDateFormat.date(locale).format(offer.validUntil!);

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: IntrinsicHeight(
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Container(
              width: 5,
              color: isVvip ? context.lfc.gold : theme.colorScheme.primary,
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
                            l10n.offerValidity(from, until),
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
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle({required this.title});

  final String title;

  @override
  Widget build(BuildContext context) {
    return Text(title, style: Theme.of(context).textTheme.titleMedium);
  }
}

class _EmptySection extends StatelessWidget {
  const _EmptySection({required this.icon, required this.message});

  final IconData icon;
  final String message;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            Icon(icon, size: 34, color: theme.colorScheme.onSurfaceVariant),
            const SizedBox(height: 12),
            Text(
              message,
              textAlign: TextAlign.center,
              style: theme.textTheme.bodyMedium?.copyWith(
                color: theme.colorScheme.onSurfaceVariant,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _UnavailableCard extends StatelessWidget {
  const _UnavailableCard({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            Icon(
              Icons.credit_card_off_outlined,
              color: context.lfc.gold,
              size: 40,
            ),
            const SizedBox(height: 14),
            Text(
              message,
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.titleMedium,
            ),
          ],
        ),
      ),
    );
  }
}
