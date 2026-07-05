class StandingRow {
  const StandingRow({
    required this.position,
    required this.clubName,
    required this.played,
    required this.won,
    required this.drawn,
    required this.lost,
    required this.goalsFor,
    required this.goalsAgainst,
    required this.goalDifference,
    required this.points,
    required this.isOwnClub,
  });

  factory StandingRow.fromJson(Map<String, dynamic> json) {
    return StandingRow(
      position: (json['position'] as num?)?.toInt() ?? 0,
      clubName: json['club_name'] as String? ?? '',
      played: (json['played'] as num?)?.toInt() ?? 0,
      won: (json['won'] as num?)?.toInt() ?? 0,
      drawn: (json['drawn'] as num?)?.toInt() ?? 0,
      lost: (json['lost'] as num?)?.toInt() ?? 0,
      goalsFor: (json['goals_for'] as num?)?.toInt() ?? 0,
      goalsAgainst: (json['goals_against'] as num?)?.toInt() ?? 0,
      goalDifference: (json['goal_difference'] as num?)?.toInt() ?? 0,
      points: (json['points'] as num?)?.toInt() ?? 0,
      isOwnClub: json['is_own_club'] as bool? ?? false,
    );
  }

  final int position;
  final String clubName;
  final int played;
  final int won;
  final int drawn;
  final int lost;
  final int goalsFor;
  final int goalsAgainst;
  final int goalDifference;
  final int points;
  final bool isOwnClub;
}
