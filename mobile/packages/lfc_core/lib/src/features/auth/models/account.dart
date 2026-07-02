class Account {
  const Account({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    required this.whatsapp,
    required this.isVvip,
    required this.accountType,
    required this.accountBalance,
  });

  factory Account.fromJson(Map<String, dynamic> json) {
    return Account(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      phone: json['phone'] as String?,
      whatsapp: json['whatsapp'] as String?,
      isVvip: json['is_vvip'] as bool? ?? false,
      accountType: json['account_type'] as String?,
      accountBalance: (json['account_balance'] as num?)?.toInt() ?? 0,
    );
  }

  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? whatsapp;
  final bool isVvip;
  final String? accountType;
  final int accountBalance;
}
