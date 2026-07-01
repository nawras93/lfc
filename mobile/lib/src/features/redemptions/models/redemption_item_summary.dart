class RedemptionItemSummary {
  const RedemptionItemSummary({
    required this.id,
    required this.name,
    required this.description,
    required this.type,
    required this.pointsCost,
    required this.inStock,
  });

  factory RedemptionItemSummary.fromJson(Map<String, dynamic> json) {
    return RedemptionItemSummary(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      description: json['description'] as String?,
      type: json['type'] as String? ?? '',
      pointsCost: (json['points_cost'] as num?)?.toInt() ?? 0,
      inStock: json['in_stock'] as bool? ?? false,
    );
  }

  final int id;
  final String name;
  final String? description;
  final String type;
  final int pointsCost;
  final bool inStock;
}
