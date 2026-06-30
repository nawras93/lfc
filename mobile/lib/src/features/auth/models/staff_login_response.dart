import 'staff_user.dart';

class StaffLoginResponse {
  const StaffLoginResponse({
    required this.token,
    required this.user,
  });

  factory StaffLoginResponse.fromJson(Map<String, dynamic> json) {
    return StaffLoginResponse(
      token: json['token'] as String,
      user: StaffUser.fromJson(json['user'] as Map<String, dynamic>),
    );
  }

  final String token;
  final StaffUser user;
}
