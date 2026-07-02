class OfferSummary {
  const OfferSummary({
    required this.id,
    required this.title,
    required this.body,
    required this.audience,
    required this.validFrom,
    required this.validUntil,
  });

  factory OfferSummary.fromJson(Map<String, dynamic> json) {
    return OfferSummary(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      body: json['body'] as String? ?? '',
      audience: json['audience'] as String? ?? '',
      validFrom: json['valid_from'] == null
          ? null
          : DateTime.parse(json['valid_from'] as String),
      validUntil: json['valid_until'] == null
          ? null
          : DateTime.parse(json['valid_until'] as String),
    );
  }

  final int id;
  final String title;
  final String body;
  final String audience;
  final DateTime? validFrom;
  final DateTime? validUntil;
}
