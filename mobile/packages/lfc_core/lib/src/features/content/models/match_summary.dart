class MatchSummary {
  const MatchSummary({
    required this.id,
    required this.opponent,
    required this.competition,
    required this.isHome,
    required this.venue,
    required this.kickoffAt,
    required this.status,
    required this.ourScore,
    required this.opponentScore,
  });

  factory MatchSummary.fromJson(Map<String, dynamic> json) {
    return MatchSummary(
      id: json['id'] as int,
      opponent: json['opponent'] as String? ?? '',
      competition: json['competition'] as String? ?? '',
      isHome: json['is_home'] as bool? ?? false,
      venue: json['venue'] as String?,
      kickoffAt: DateTime.parse(json['kickoff_at'] as String),
      status: json['status'] as String? ?? '',
      ourScore: (json['our_score'] as num?)?.toInt(),
      opponentScore: (json['opponent_score'] as num?)?.toInt(),
    );
  }

  final int id;
  final String opponent;
  final String competition;
  final bool isHome;
  final String? venue;
  final DateTime kickoffAt;
  final String status;
  final int? ourScore;
  final int? opponentScore;
}
