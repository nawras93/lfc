import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../core/formatting/app_date_format.dart';
import '../../../providers.dart';
import '../../../theme/widgets/brand_app_bar.dart';
import 'content_states.dart';

class NewsDetailScreen extends ConsumerWidget {
  const NewsDetailScreen({super.key, required this.newsId});

  final int newsId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final articleAsync = ref.watch(newsArticleProvider(newsId));
    final l10n = AppLocalizations.of(context)!;
    final locale = Localizations.localeOf(context).languageCode;

    return Scaffold(
      appBar: const BrandAppBar(showBack: true),
      body: articleAsync.when(
        loading: () => const ContentLoader(),
        error: (error, _) => ContentErrorState(
          message: error.toString(),
          retryLabel: l10n.retryButton,
          onRetry: () => ref.invalidate(newsArticleProvider(newsId)),
        ),
        data: (article) => ListView(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
          children: [
            if (article.imageUrl != null) ...[
              ClipRRect(
                borderRadius: BorderRadius.circular(20),
                child: AspectRatio(
                  aspectRatio: 16 / 9,
                  child: Image.network(
                    article.imageUrl!,
                    fit: BoxFit.cover,
                    errorBuilder: (_, _, _) =>
                        const ColoredBox(color: Colors.transparent),
                  ),
                ),
              ),
              const SizedBox(height: 18),
            ],
            Text(
              AppDateFormat.date(locale).format(article.publishedAt),
              style: Theme.of(context).textTheme.labelMedium?.copyWith(
                color: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              article.title,
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            if (article.excerpt.isNotEmpty) ...[
              const SizedBox(height: 10),
              Text(
                article.excerpt,
                style: Theme.of(context).textTheme.titleMedium,
              ),
            ],
            const SizedBox(height: 16),
            Text(article.body, style: Theme.of(context).textTheme.bodyLarge),
          ],
        ),
      ),
    );
  }
}
