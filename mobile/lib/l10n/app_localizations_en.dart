// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for English (`en`).
class AppLocalizationsEn extends AppLocalizations {
  AppLocalizationsEn([String locale = 'en']) : super(locale);

  @override
  String get appTitle => 'LFC';

  @override
  String get loginTitle => 'Sign in';

  @override
  String get loginSubtitle => 'Use your parent or VVIP account to continue.';

  @override
  String get emailLabel => 'Email';

  @override
  String get passwordLabel => 'Password';

  @override
  String get inviteTokenLabel => 'Invite token';

  @override
  String get signInButton => 'Sign in';

  @override
  String get acceptInviteButton => 'Accept invite';

  @override
  String get openAcceptInvite => 'Set password from invite';

  @override
  String get backToLogin => 'Back to sign in';

  @override
  String get logoutButton => 'Log out';

  @override
  String get languageToggle => 'AR';

  @override
  String get languageLabel => 'Language';

  @override
  String get homeTitle => 'Account';

  @override
  String get accountTypeLabel => 'Account type';

  @override
  String get vvipStatusLabel => 'VVIP';

  @override
  String get balanceLabel => 'Account balance';

  @override
  String get yesText => 'Yes';

  @override
  String get noText => 'No';

  @override
  String get requiredField => 'This field is required.';

  @override
  String get invalidEmail => 'Enter a valid email address.';

  @override
  String get passwordMinLength => 'Password must be at least 8 characters.';

  @override
  String get sessionExpired => 'Your session expired. Please sign in again.';

  @override
  String get genericError => 'Something went wrong. Please try again.';

  @override
  String get networkError =>
      'Cannot reach the server. Check the API URL and connection.';

  @override
  String get serverError =>
      'The server is unavailable right now. Please try again shortly.';

  @override
  String get loadingText => 'Loading...';

  @override
  String welcomeLabel(Object name) {
    return 'Welcome, $name';
  }

  @override
  String get acceptInviteTitle => 'Accept invitation';

  @override
  String get acceptInviteSubtitle => 'Set a password to activate your account.';

  @override
  String get savePasswordButton => 'Save password';

  @override
  String get demoApiHint =>
      'Demo API URL is configured with --dart-define=API_BASE_URL.';

  @override
  String get notAvailableValue => 'Not available';
}
