class NewsArticle {
  const NewsArticle({
    required this.id,
    required this.title,
    required this.excerpt,
    required this.body,
    required this.imageUrl,
    required this.publishedAt,
  });

  factory NewsArticle.fromJson(Map<String, dynamic> json) {
    return NewsArticle(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      excerpt: json['excerpt'] as String? ?? '',
      body: json['body'] as String? ?? '',
      imageUrl: json['image_url'] as String?,
      publishedAt: DateTime.parse(json['published_at'] as String),
    );
  }

  final int id;
  final String title;
  final String excerpt;
  final String body;
  final String? imageUrl;
  final DateTime publishedAt;
}
