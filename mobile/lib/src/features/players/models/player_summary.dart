class PlayerSummary {
  const PlayerSummary({
    required this.id,
    required this.fullName,
    required this.teamName,
    required this.playingPosition,
    required this.pointsBalance,
    required this.progress,
    required this.isPlayer,
  });

  factory PlayerSummary.fromJson(Map<String, dynamic> json) {
    return PlayerSummary(
      id: json['id'] as int,
      fullName: json['full_name'] as String? ?? '',
      teamName: json['team_name'] as String?,
      playingPosition: json['playing_position'] as String? ?? '',
      pointsBalance: (json['points_balance'] as num?)?.toInt() ?? 0,
      progress: json['progress'] as String? ?? '',
      isPlayer: json['is_player'] as bool? ?? false,
    );
  }

  final int id;
  final String fullName;
  final String? teamName;
  final String playingPosition;
  final int pointsBalance;
  final String progress;
  final bool isPlayer;
}
