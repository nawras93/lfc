class FixtureSummary {
  const FixtureSummary({
    required this.id,
    required this.teamName,
    required this.opponent,
    required this.venue,
    required this.kickoffAt,
    required this.scanClosesAt,
  });

  factory FixtureSummary.fromJson(Map<String, dynamic> json) {
    return FixtureSummary(
      id: json['id'] as int,
      teamName: json['team_name'] as String?,
      opponent: json['opponent'] as String? ?? '',
      venue: json['venue'] as String?,
      kickoffAt: json['kickoff_at'] == null
          ? null
          : DateTime.parse(json['kickoff_at'] as String),
      scanClosesAt: json['scan_closes_at'] == null
          ? null
          : DateTime.parse(json['scan_closes_at'] as String),
    );
  }

  final int id;
  final String? teamName;
  final String opponent;
  final String? venue;
  final DateTime? kickoffAt;
  final DateTime? scanClosesAt;
}
