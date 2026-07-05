import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../core/formatting/app_date_format.dart';
import '../../../providers.dart';
import '../models/news_summary.dart';
import '../util/media_url.dart';
import 'content_states.dart';
import 'news_detail_screen.dart';

class HomeNewsScreen extends ConsumerWidget {
  const HomeNewsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;
    final news = ref.watch(newsProvider);

    return news.when(
      loading: () => const ContentLoader(),
      error: (error, _) => ContentErrorState(
        message: error.toString(),
        retryLabel: l10n.retryButton,
        onRetry: () => ref.invalidate(newsProvider),
      ),
      data: (items) {
        if (items.isEmpty) {
          return ContentEmptyState(
            icon: Icons.newspaper_outlined,
            title: l10n.newsEmptyState,
          );
        }

        return RefreshIndicator(
          onRefresh: () async => ref.refresh(newsProvider.future),
          child: ListView.separated(
            key: const Key('supporter-news-list'),
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
            itemCount: items.length + 1,
            separatorBuilder: (_, _) => const SizedBox(height: 14),
            itemBuilder: (context, index) {
              if (index == 0) {
                return Text(
                  l10n.latestNewsTitle,
                  style: Theme.of(context).textTheme.headlineSmall,
                );
              }

              return _NewsCard(article: items[index - 1]);
            },
          ),
        );
      },
    );
  }
}

class _NewsCard extends ConsumerWidget {
  const _NewsCard({required this.article});

  final NewsSummary article;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final theme = Theme.of(context);
    final config = ref.watch(appConfigProvider);
    final imageUrl = article.imageUrl == null
        ? null
        : resolveMediaUrl(article.imageUrl!, config.apiBaseUrl);

    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () {
          Navigator.of(context).push(
            MaterialPageRoute<void>(
              builder: (_) => NewsDetailScreen(newsId: article.id),
            ),
          );
        },
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (imageUrl != null)
              SizedBox(
                height: 188,
                child: Image.network(
                  imageUrl,
                  fit: BoxFit.cover,
                  errorBuilder: (_, _, _) =>
                      const ColoredBox(color: Colors.transparent),
                ),
              ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    AppDateFormat.westernDate().format(article.publishedAt),
                    style: theme.textTheme.labelMedium?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(article.title, style: theme.textTheme.titleLarge),
                  if (article.excerpt.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Text(
                      article.excerpt,
                      maxLines: 3,
                      overflow: TextOverflow.ellipsis,
                      style: theme.textTheme.bodyMedium,
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
