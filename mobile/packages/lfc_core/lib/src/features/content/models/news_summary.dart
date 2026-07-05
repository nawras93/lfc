class NewsSummary {
  const NewsSummary({
    required this.id,
    required this.title,
    required this.excerpt,
    required this.imageUrl,
    required this.publishedAt,
  });

  factory NewsSummary.fromJson(Map<String, dynamic> json) {
    return NewsSummary(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      excerpt: json['excerpt'] as String? ?? '',
      imageUrl: json['image_url'] as String?,
      publishedAt: DateTime.parse(json['published_at'] as String),
    );
  }

  final int id;
  final String title;
  final String excerpt;
  final String? imageUrl;
  final DateTime publishedAt;
}
