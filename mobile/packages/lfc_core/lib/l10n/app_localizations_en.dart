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
      'Demo API URL auto-defaults to localhost on iOS and 10.0.2.2 on Android. Override it with --dart-define=API_BASE_URL.';

  @override
  String get notAvailableValue => 'Not available';

  @override
  String get playersTab => 'Players';

  @override
  String get qrTab => 'QR';

  @override
  String get rewardsTab => 'Rewards';

  @override
  String get offersTab => 'Offers';

  @override
  String get staffScannerEntry => 'Staff scanner';

  @override
  String get pointsUnit => 'pts';

  @override
  String get playersEmptyTitle => 'No linked players yet';

  @override
  String get playersEmptyBody =>
      'This account does not have player links for the demo.';

  @override
  String get accountPointsHistoryTitle => 'Account points history';

  @override
  String playerPointsHistoryTitle(Object name) {
    return '$name points history';
  }

  @override
  String get noTransactionsEmpty => 'No points transactions yet.';

  @override
  String get transactionSourceScan => 'Attendance scan';

  @override
  String get transactionSourceRedemption => 'Redemption';

  @override
  String get transactionTypeEarn => 'Earn';

  @override
  String get transactionTypeRedeem => 'Redeem';

  @override
  String get transactionTypeExpire => 'Expire';

  @override
  String get transactionTypeAdjust => 'Adjust';

  @override
  String get transactionTypeReverse => 'Reverse';

  @override
  String get teamLabel => 'Team';

  @override
  String get positionLabel => 'Position';

  @override
  String get progressLabel => 'Progress';

  @override
  String get pointsHistoryAction => 'Points history';

  @override
  String get retryButton => 'Retry';

  @override
  String get qrScreenTitle => 'Show this QR at the gate';

  @override
  String get qrScreenSubtitle =>
      'Staff will scan it to credit eligible players for the open fixture.';

  @override
  String qrExpiresIn(int seconds) {
    return 'Refreshes in ${seconds}s';
  }

  @override
  String get refreshQrButton => 'Refresh QR';

  @override
  String get offersEmpty => 'No offers are available right now.';

  @override
  String get vvipBadge => 'VVIP';

  @override
  String offerValidity(Object from, Object until) {
    return 'Valid from $from until $until';
  }

  @override
  String get selectPlayerBeforeRedeem => 'Select a player before redeeming.';

  @override
  String get redeemForPlayerLabel => 'Redeem for player';

  @override
  String get catalogTab => 'Catalog';

  @override
  String get vouchersTab => 'Vouchers';

  @override
  String get redeemButton => 'Redeem';

  @override
  String get outOfStockLabel => 'Out of stock';

  @override
  String get redeemLinkedPlayerError =>
      'This player is not linked to your account.';

  @override
  String get redeemVvipOnlyError =>
      'Account redemption requires a VVIP client account.';

  @override
  String get redeemInsufficientError =>
      'You don\'t have enough points for this reward.';

  @override
  String get redeemUnavailableError => 'This reward is unavailable right now.';

  @override
  String get redemptionTypeFeeDiscount => 'Fee discount';

  @override
  String get redemptionTypeEvent => 'Event';

  @override
  String get redemptionTypeMerch => 'Merchandise';

  @override
  String get accountLabel => 'Account';

  @override
  String get voucherDialogTitle => 'Voucher issued';

  @override
  String get voucherCodeLabel => 'Voucher code';

  @override
  String get voucherStatusIssued => 'Issued';

  @override
  String get voucherStatusFulfilled => 'Fulfilled';

  @override
  String get voucherStatusCancelled => 'Cancelled';

  @override
  String get pointsSpentLabel => 'Points spent';

  @override
  String get statusLabel => 'Status';

  @override
  String get playerLabel => 'Player';

  @override
  String get closeButton => 'Close';

  @override
  String get vouchersEmpty => 'No vouchers yet.';

  @override
  String get staffLoginTitle => 'Staff scanner login';

  @override
  String get staffLoginSubtitle => 'Use a staff account with scanner access.';

  @override
  String get staffLoginButton => 'Sign in as staff';

  @override
  String get backToParentLogin => 'Back to parent login';

  @override
  String get staffScannerTitle => 'Scanner';

  @override
  String get fixtureLabel => 'Fixture';

  @override
  String get manualTokenTitle => 'Manual token entry';

  @override
  String get tokenLabel => 'QR token';

  @override
  String get manualTokenHint =>
      'Paste the parent token here for simulator demos.';

  @override
  String get submitScanButton => 'Submit scan';

  @override
  String get hideCameraButton => 'Hide camera';

  @override
  String get openCameraButton => 'Open camera';

  @override
  String get scanSuccessTitle => 'Scan credited';

  @override
  String get totalPointsLabel => 'Total points';

  @override
  String get selectFixtureHint => 'Select a fixture first.';

  @override
  String get staffForbiddenError => 'You do not have scanner access.';

  @override
  String get scanAlreadyUsedError => 'Already scanned for this match.';

  @override
  String get scanInvalidQrError => 'The QR token is invalid or expired.';

  @override
  String get scanFixtureClosedError => 'This fixture is not open for scanning.';

  @override
  String get scanNoLinkedPlayerError =>
      'No linked player is on this fixture\'s team.';

  @override
  String get scanValidationError => 'The scan could not be completed.';

  @override
  String get themeLabel => 'Theme';

  @override
  String get memberLabel => 'Member';

  @override
  String get staffRole => 'Staff';

  @override
  String get loginTagline => 'Your academy. Your points. Your matchday.';

  @override
  String get homeNavLabel => 'Home';

  @override
  String get matchesNavLabel => 'Matches';

  @override
  String get membershipNavLabel => 'Membership';

  @override
  String get latestNewsTitle => 'Latest news';

  @override
  String get fixturesTitle => 'Fixtures';

  @override
  String get resultsTitle => 'Results';

  @override
  String get tableTitle => 'Table';

  @override
  String get newsEmptyState => 'No news has been published yet.';

  @override
  String get fixturesEmptyState => 'No upcoming fixtures right now.';

  @override
  String get resultsEmptyState => 'No results available yet.';

  @override
  String get tableEmptyState => 'The league table is not available yet.';

  @override
  String get membershipSignInPrompt => 'Sign in to access your membership';

  @override
  String get membershipComingSoon => 'Your membership - coming soon';

  @override
  String get clubColumnLabel => 'Club';

  @override
  String get positionColumnShort => 'Pos';

  @override
  String get playedColumnShort => 'P';

  @override
  String get wonColumnShort => 'W';

  @override
  String get drawnColumnShort => 'D';

  @override
  String get lostColumnShort => 'L';

  @override
  String get goalDifferenceColumnShort => 'GD';

  @override
  String get pointsColumnShort => 'Pts';

  @override
  String get venueLabel => 'Venue';

  @override
  String get homeMatchChip => 'Home';

  @override
  String get awayMatchChip => 'Away';

  @override
  String get createAccountButton => 'Create account';

  @override
  String get nameLabel => 'Full name';

  @override
  String get confirmPasswordLabel => 'Confirm password';

  @override
  String get passwordsDoNotMatch => 'Passwords don\'t match.';

  @override
  String get phoneLabel => 'Phone';

  @override
  String get walletTitle => 'Wallet';

  @override
  String get memberDiscountLabel => 'ACADEMY REGISTRATION DISCOUNT';

  @override
  String discountPercentValue(int percent) {
    return '$percent%';
  }

  @override
  String get redeemableTowardRegistration =>
      'redeemable toward academy registration';

  @override
  String discountCapNote(int cap) {
    return 'of $cap% maximum';
  }

  @override
  String get memberQrTitle => 'Match day QR';

  @override
  String get memberQrSubtitle => 'Show this at the match to earn 0.5%';

  @override
  String get discountHistoryTitle => 'Discount history';

  @override
  String get discountHistoryEmpty => 'No discount transactions yet.';

  @override
  String discountHistoryFormat(Object value) {
    return '$value%';
  }

  @override
  String get vvipCardComingSoon => 'Your VVIP membership card — coming soon';

  @override
  String get signOutButton => 'Sign out';
}
