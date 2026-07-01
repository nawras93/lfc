import 'account.dart';

class LoginResponse {
  const LoginResponse({required this.token, required this.account});

  factory LoginResponse.fromJson(Map<String, dynamic> json) {
    return LoginResponse(
      token: json['token'] as String,
      account: Account.fromJson(
        (json['parent'] ?? json['account']) as Map<String, dynamic>,
      ),
    );
  }

  final String token;
  final Account account;
}
