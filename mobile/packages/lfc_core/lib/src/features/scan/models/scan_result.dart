class ScanCredit {
  const ScanCredit({
    required this.playerId,
    required this.playerName,
    required this.points,
  });

  factory ScanCredit.fromJson(Map<String, dynamic> json) {
    return ScanCredit(
      playerId: json['player_id'] as int,
      playerName: json['player_name'] as String? ?? '',
      points: (json['points'] as num?)?.toInt() ?? 0,
    );
  }

  final int playerId;
  final String playerName;
  final int points;
}

class ScanResult {
  const ScanResult({
    required this.scanId,
    required this.credited,
    required this.totalPoints,
    this.discountAddedPercent,
    this.discountPercent,
    this.discountCapPercent,
  });

  factory ScanResult.fromJson(Map<String, dynamic> json) {
    final credited = json['credited'] as List<dynamic>? ?? const [];

    return ScanResult(
      scanId: json['scan_id'] as int,
      credited: credited
          .whereType<Map<String, dynamic>>()
          .map(ScanCredit.fromJson)
          .toList(),
      totalPoints: (json['total_points'] as num?)?.toInt() ?? 0,
      discountAddedPercent: (json['discount_added_percent'] as num?)
          ?.toDouble(),
      discountPercent: (json['discount_percent'] as num?)?.toDouble(),
      discountCapPercent: (json['discount_cap_percent'] as num?)?.toDouble(),
    );
  }

  final int scanId;
  final List<ScanCredit> credited;
  final int totalPoints;
  final double? discountAddedPercent;
  final double? discountPercent;
  final double? discountCapPercent;

  bool get isDiscountScan => discountPercent != null;
}
