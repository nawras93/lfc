import 'membership_benefit.dart';
import 'membership_tier_info.dart';

class MembershipCard {
  const MembershipCard({
    required this.tier,
    required this.memberNumber,
    required this.validUntil,
    required this.benefits,
  });

  factory MembershipCard.fromJson(Map<String, dynamic> json) {
    return MembershipCard(
      tier: MembershipTierInfo.fromJson(
        json['tier'] as Map<String, dynamic>? ?? const <String, dynamic>{},
      ),
      memberNumber: json['member_number'] as String?,
      validUntil: _parseDate(json['valid_until'] as String?),
      benefits: (json['benefits'] as List<dynamic>? ?? const [])
          .whereType<Map<String, dynamic>>()
          .map(MembershipBenefit.fromJson)
          .toList(),
    );
  }

  final MembershipTierInfo tier;
  final String? memberNumber;
  final DateTime? validUntil;
  final List<MembershipBenefit> benefits;

  static DateTime? _parseDate(String? value) {
    if (value == null || value.isEmpty) {
      return null;
    }

    return DateTime.tryParse(value);
  }
}
