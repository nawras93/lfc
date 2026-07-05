class MembershipTierInfo {
  const MembershipTierInfo({
    required this.name,
    required this.level,
    this.accentColor,
  });

  factory MembershipTierInfo.fromJson(Map<String, dynamic> json) {
    return MembershipTierInfo(
      name: json['name'] as String? ?? '',
      level: (json['level'] as num?)?.toInt() ?? 0,
      accentColor: json['accent_color'] as String?,
    );
  }

  final String name;
  final int level;
  final String? accentColor;
}
