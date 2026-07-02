class StaffUser {
  const StaffUser({required this.id, required this.name, required this.email});

  factory StaffUser.fromJson(Map<String, dynamic> json) {
    return StaffUser(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
    );
  }

  final int id;
  final String name;
  final String email;
}
