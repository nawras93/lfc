class RedemptionHistoryItem {
  const RedemptionHistoryItem({
    required this.id,
    required this.voucherCode,
    required this.pointsSpent,
    required this.status,
    required this.itemName,
    required this.itemType,
    required this.playerName,
    required this.fulfilledAt,
    required this.createdAt,
  });

  factory RedemptionHistoryItem.fromJson(Map<String, dynamic> json) {
    final item = json['item'] as Map<String, dynamic>? ?? const <String, dynamic>{};

    return RedemptionHistoryItem(
      id: json['id'] as int,
      voucherCode: json['voucher_code'] as String? ?? '',
      pointsSpent: (json['points_spent'] as num?)?.toInt() ?? 0,
      status: json['status'] as String? ?? '',
      itemName: item['name'] as String? ?? '',
      itemType: item['type'] as String? ?? '',
      playerName: json['player_name'] as String?,
      fulfilledAt: json['fulfilled_at'] == null
          ? null
          : DateTime.parse(json['fulfilled_at'] as String),
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }

  final int id;
  final String voucherCode;
  final int pointsSpent;
  final String status;
  final String itemName;
  final String itemType;
  final String? playerName;
  final DateTime? fulfilledAt;
  final DateTime createdAt;
}
