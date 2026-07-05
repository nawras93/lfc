class MembershipBenefit {
  const MembershipBenefit({this.title, this.description, this.icon});

  factory MembershipBenefit.fromJson(Map<String, dynamic> json) {
    return MembershipBenefit(
      title: json['title'] as String?,
      description: json['description'] as String?,
      icon: json['icon'] as String?,
    );
  }

  final String? title;
  final String? description;
  final String? icon;
}
