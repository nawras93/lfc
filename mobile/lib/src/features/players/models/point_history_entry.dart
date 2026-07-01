class PointHistoryEntry {
  const PointHistoryEntry({
    required this.id,
    required this.points,
    required this.type,
    required this.reason,
    required this.source,
    required this.createdAt,
  });

  factory PointHistoryEntry.fromJson(Map<String, dynamic> json) {
    return PointHistoryEntry(
      id: json['id'] as int,
      points: (json['points'] as num?)?.toInt() ?? 0,
      type: json['type'] as String? ?? '',
      reason: json['reason'] as String?,
      source: json['source'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }

  final int id;
  final int points;
  final String type;
  final String? reason;
  final String? source;
  final DateTime createdAt;
}
