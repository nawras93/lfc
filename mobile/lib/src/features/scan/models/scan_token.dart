class ScanToken {
  const ScanToken({
    required this.token,
    required this.expiresAt,
  });

  factory ScanToken.fromJson(Map<String, dynamic> json) {
    return ScanToken(
      token: json['token'] as String? ?? '',
      expiresAt: DateTime.parse(json['expires_at'] as String),
    );
  }

  final String token;
  final DateTime expiresAt;
}
