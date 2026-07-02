import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/intl.dart' as intl;

import 'app_localizations_ar.dart';
import 'app_localizations_en.dart';

// ignore_for_file: type=lint

/// Callers can lookup localized strings with an instance of AppLocalizations
/// returned by `AppLocalizations.of(context)`.
///
/// Applications need to include `AppLocalizations.delegate()` in their app's
/// `localizationDelegates` list, and the locales they support in the app's
/// `supportedLocales` list. For example:
///
/// ```dart
/// import 'l10n/app_localizations.dart';
///
/// return MaterialApp(
///   localizationsDelegates: AppLocalizations.localizationsDelegates,
///   supportedLocales: AppLocalizations.supportedLocales,
///   home: MyApplicationHome(),
/// );
/// ```
///
/// ## Update pubspec.yaml
///
/// Please make sure to update your pubspec.yaml to include the following
/// packages:
///
/// ```yaml
/// dependencies:
///   # Internationalization support.
///   flutter_localizations:
///     sdk: flutter
///   intl: any # Use the pinned version from flutter_localizations
///
///   # Rest of dependencies
/// ```
///
/// ## iOS Applications
///
/// iOS applications define key application metadata, including supported
/// locales, in an Info.plist file that is built into the application bundle.
/// To configure the locales supported by your app, you’ll need to edit this
/// file.
///
/// First, open your project’s ios/Runner.xcworkspace Xcode workspace file.
/// Then, in the Project Navigator, open the Info.plist file under the Runner
/// project’s Runner folder.
///
/// Next, select the Information Property List item, select Add Item from the
/// Editor menu, then select Localizations from the pop-up menu.
///
/// Select and expand the newly-created Localizations item then, for each
/// locale your application supports, add a new item and select the locale
/// you wish to add from the pop-up menu in the Value field. This list should
/// be consistent with the languages listed in the AppLocalizations.supportedLocales
/// property.
abstract class AppLocalizations {
  AppLocalizations(String locale)
    : localeName = intl.Intl.canonicalizedLocale(locale.toString());

  final String localeName;

  static AppLocalizations? of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations);
  }

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  /// A list of this localizations delegate along with the default localizations
  /// delegates.
  ///
  /// Returns a list of localizations delegates containing this delegate along with
  /// GlobalMaterialLocalizations.delegate, GlobalCupertinoLocalizations.delegate,
  /// and GlobalWidgetsLocalizations.delegate.
  ///
  /// Additional delegates can be added by appending to this list in
  /// MaterialApp. This list does not have to be used at all if a custom list
  /// of delegates is preferred or required.
  static const List<LocalizationsDelegate<dynamic>> localizationsDelegates =
      <LocalizationsDelegate<dynamic>>[
        delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
      ];

  /// A list of this localizations delegate's supported locales.
  static const List<Locale> supportedLocales = <Locale>[
    Locale('ar'),
    Locale('en'),
  ];

  /// No description provided for @appTitle.
  ///
  /// In en, this message translates to:
  /// **'LFC'**
  String get appTitle;

  /// No description provided for @loginTitle.
  ///
  /// In en, this message translates to:
  /// **'Sign in'**
  String get loginTitle;

  /// No description provided for @loginSubtitle.
  ///
  /// In en, this message translates to:
  /// **'Use your parent or VVIP account to continue.'**
  String get loginSubtitle;

  /// No description provided for @emailLabel.
  ///
  /// In en, this message translates to:
  /// **'Email'**
  String get emailLabel;

  /// No description provided for @passwordLabel.
  ///
  /// In en, this message translates to:
  /// **'Password'**
  String get passwordLabel;

  /// No description provided for @inviteTokenLabel.
  ///
  /// In en, this message translates to:
  /// **'Invite token'**
  String get inviteTokenLabel;

  /// No description provided for @signInButton.
  ///
  /// In en, this message translates to:
  /// **'Sign in'**
  String get signInButton;

  /// No description provided for @acceptInviteButton.
  ///
  /// In en, this message translates to:
  /// **'Accept invite'**
  String get acceptInviteButton;

  /// No description provided for @openAcceptInvite.
  ///
  /// In en, this message translates to:
  /// **'Set password from invite'**
  String get openAcceptInvite;

  /// No description provided for @backToLogin.
  ///
  /// In en, this message translates to:
  /// **'Back to sign in'**
  String get backToLogin;

  /// No description provided for @logoutButton.
  ///
  /// In en, this message translates to:
  /// **'Log out'**
  String get logoutButton;

  /// No description provided for @languageToggle.
  ///
  /// In en, this message translates to:
  /// **'AR'**
  String get languageToggle;

  /// No description provided for @languageLabel.
  ///
  /// In en, this message translates to:
  /// **'Language'**
  String get languageLabel;

  /// No description provided for @homeTitle.
  ///
  /// In en, this message translates to:
  /// **'Account'**
  String get homeTitle;

  /// No description provided for @accountTypeLabel.
  ///
  /// In en, this message translates to:
  /// **'Account type'**
  String get accountTypeLabel;

  /// No description provided for @vvipStatusLabel.
  ///
  /// In en, this message translates to:
  /// **'VVIP'**
  String get vvipStatusLabel;

  /// No description provided for @balanceLabel.
  ///
  /// In en, this message translates to:
  /// **'Account balance'**
  String get balanceLabel;

  /// No description provided for @yesText.
  ///
  /// In en, this message translates to:
  /// **'Yes'**
  String get yesText;

  /// No description provided for @noText.
  ///
  /// In en, this message translates to:
  /// **'No'**
  String get noText;

  /// No description provided for @requiredField.
  ///
  /// In en, this message translates to:
  /// **'This field is required.'**
  String get requiredField;

  /// No description provided for @invalidEmail.
  ///
  /// In en, this message translates to:
  /// **'Enter a valid email address.'**
  String get invalidEmail;

  /// No description provided for @passwordMinLength.
  ///
  /// In en, this message translates to:
  /// **'Password must be at least 8 characters.'**
  String get passwordMinLength;

  /// No description provided for @sessionExpired.
  ///
  /// In en, this message translates to:
  /// **'Your session expired. Please sign in again.'**
  String get sessionExpired;

  /// No description provided for @genericError.
  ///
  /// In en, this message translates to:
  /// **'Something went wrong. Please try again.'**
  String get genericError;

  /// No description provided for @networkError.
  ///
  /// In en, this message translates to:
  /// **'Cannot reach the server. Check the API URL and connection.'**
  String get networkError;

  /// No description provided for @serverError.
  ///
  /// In en, this message translates to:
  /// **'The server is unavailable right now. Please try again shortly.'**
  String get serverError;

  /// No description provided for @loadingText.
  ///
  /// In en, this message translates to:
  /// **'Loading...'**
  String get loadingText;

  /// No description provided for @welcomeLabel.
  ///
  /// In en, this message translates to:
  /// **'Welcome, {name}'**
  String welcomeLabel(Object name);

  /// No description provided for @acceptInviteTitle.
  ///
  /// In en, this message translates to:
  /// **'Accept invitation'**
  String get acceptInviteTitle;

  /// No description provided for @acceptInviteSubtitle.
  ///
  /// In en, this message translates to:
  /// **'Set a password to activate your account.'**
  String get acceptInviteSubtitle;

  /// No description provided for @savePasswordButton.
  ///
  /// In en, this message translates to:
  /// **'Save password'**
  String get savePasswordButton;

  /// No description provided for @demoApiHint.
  ///
  /// In en, this message translates to:
  /// **'Demo API URL auto-defaults to localhost on iOS and 10.0.2.2 on Android. Override it with --dart-define=API_BASE_URL.'**
  String get demoApiHint;

  /// No description provided for @notAvailableValue.
  ///
  /// In en, this message translates to:
  /// **'Not available'**
  String get notAvailableValue;

  /// No description provided for @playersTab.
  ///
  /// In en, this message translates to:
  /// **'Players'**
  String get playersTab;

  /// No description provided for @qrTab.
  ///
  /// In en, this message translates to:
  /// **'QR'**
  String get qrTab;

  /// No description provided for @rewardsTab.
  ///
  /// In en, this message translates to:
  /// **'Rewards'**
  String get rewardsTab;

  /// No description provided for @offersTab.
  ///
  /// In en, this message translates to:
  /// **'Offers'**
  String get offersTab;

  /// No description provided for @staffScannerEntry.
  ///
  /// In en, this message translates to:
  /// **'Staff scanner'**
  String get staffScannerEntry;

  /// No description provided for @pointsUnit.
  ///
  /// In en, this message translates to:
  /// **'pts'**
  String get pointsUnit;

  /// No description provided for @playersEmptyTitle.
  ///
  /// In en, this message translates to:
  /// **'No linked players yet'**
  String get playersEmptyTitle;

  /// No description provided for @playersEmptyBody.
  ///
  /// In en, this message translates to:
  /// **'This account does not have player links for the demo.'**
  String get playersEmptyBody;

  /// No description provided for @accountPointsHistoryTitle.
  ///
  /// In en, this message translates to:
  /// **'Account points history'**
  String get accountPointsHistoryTitle;

  /// No description provided for @playerPointsHistoryTitle.
  ///
  /// In en, this message translates to:
  /// **'{name} points history'**
  String playerPointsHistoryTitle(Object name);

  /// No description provided for @noTransactionsEmpty.
  ///
  /// In en, this message translates to:
  /// **'No points transactions yet.'**
  String get noTransactionsEmpty;

  /// No description provided for @transactionSourceScan.
  ///
  /// In en, this message translates to:
  /// **'Attendance scan'**
  String get transactionSourceScan;

  /// No description provided for @transactionSourceRedemption.
  ///
  /// In en, this message translates to:
  /// **'Redemption'**
  String get transactionSourceRedemption;

  /// No description provided for @transactionTypeEarn.
  ///
  /// In en, this message translates to:
  /// **'Earn'**
  String get transactionTypeEarn;

  /// No description provided for @transactionTypeRedeem.
  ///
  /// In en, this message translates to:
  /// **'Redeem'**
  String get transactionTypeRedeem;

  /// No description provided for @transactionTypeExpire.
  ///
  /// In en, this message translates to:
  /// **'Expire'**
  String get transactionTypeExpire;

  /// No description provided for @transactionTypeAdjust.
  ///
  /// In en, this message translates to:
  /// **'Adjust'**
  String get transactionTypeAdjust;

  /// No description provided for @transactionTypeReverse.
  ///
  /// In en, this message translates to:
  /// **'Reverse'**
  String get transactionTypeReverse;

  /// No description provided for @teamLabel.
  ///
  /// In en, this message translates to:
  /// **'Team'**
  String get teamLabel;

  /// No description provided for @positionLabel.
  ///
  /// In en, this message translates to:
  /// **'Position'**
  String get positionLabel;

  /// No description provided for @progressLabel.
  ///
  /// In en, this message translates to:
  /// **'Progress'**
  String get progressLabel;

  /// No description provided for @pointsHistoryAction.
  ///
  /// In en, this message translates to:
  /// **'Points history'**
  String get pointsHistoryAction;

  /// No description provided for @retryButton.
  ///
  /// In en, this message translates to:
  /// **'Retry'**
  String get retryButton;

  /// No description provided for @qrScreenTitle.
  ///
  /// In en, this message translates to:
  /// **'Show this QR at the gate'**
  String get qrScreenTitle;

  /// No description provided for @qrScreenSubtitle.
  ///
  /// In en, this message translates to:
  /// **'Staff will scan it to credit eligible players for the open fixture.'**
  String get qrScreenSubtitle;

  /// No description provided for @qrExpiresIn.
  ///
  /// In en, this message translates to:
  /// **'Refreshes in {seconds}s'**
  String qrExpiresIn(int seconds);

  /// No description provided for @refreshQrButton.
  ///
  /// In en, this message translates to:
  /// **'Refresh QR'**
  String get refreshQrButton;

  /// No description provided for @offersEmpty.
  ///
  /// In en, this message translates to:
  /// **'No offers are available right now.'**
  String get offersEmpty;

  /// No description provided for @vvipBadge.
  ///
  /// In en, this message translates to:
  /// **'VVIP'**
  String get vvipBadge;

  /// No description provided for @offerValidity.
  ///
  /// In en, this message translates to:
  /// **'Valid from {from} until {until}'**
  String offerValidity(Object from, Object until);

  /// No description provided for @selectPlayerBeforeRedeem.
  ///
  /// In en, this message translates to:
  /// **'Select a player before redeeming.'**
  String get selectPlayerBeforeRedeem;

  /// No description provided for @redeemForPlayerLabel.
  ///
  /// In en, this message translates to:
  /// **'Redeem for player'**
  String get redeemForPlayerLabel;

  /// No description provided for @catalogTab.
  ///
  /// In en, this message translates to:
  /// **'Catalog'**
  String get catalogTab;

  /// No description provided for @vouchersTab.
  ///
  /// In en, this message translates to:
  /// **'Vouchers'**
  String get vouchersTab;

  /// No description provided for @redeemButton.
  ///
  /// In en, this message translates to:
  /// **'Redeem'**
  String get redeemButton;

  /// No description provided for @outOfStockLabel.
  ///
  /// In en, this message translates to:
  /// **'Out of stock'**
  String get outOfStockLabel;

  /// No description provided for @redeemLinkedPlayerError.
  ///
  /// In en, this message translates to:
  /// **'This player is not linked to your account.'**
  String get redeemLinkedPlayerError;

  /// No description provided for @redeemVvipOnlyError.
  ///
  /// In en, this message translates to:
  /// **'Account redemption requires a VVIP client account.'**
  String get redeemVvipOnlyError;

  /// No description provided for @redeemInsufficientError.
  ///
  /// In en, this message translates to:
  /// **'You don\'t have enough points for this reward.'**
  String get redeemInsufficientError;

  /// No description provided for @redeemUnavailableError.
  ///
  /// In en, this message translates to:
  /// **'This reward is unavailable right now.'**
  String get redeemUnavailableError;

  /// No description provided for @redemptionTypeFeeDiscount.
  ///
  /// In en, this message translates to:
  /// **'Fee discount'**
  String get redemptionTypeFeeDiscount;

  /// No description provided for @redemptionTypeEvent.
  ///
  /// In en, this message translates to:
  /// **'Event'**
  String get redemptionTypeEvent;

  /// No description provided for @redemptionTypeMerch.
  ///
  /// In en, this message translates to:
  /// **'Merchandise'**
  String get redemptionTypeMerch;

  /// No description provided for @accountLabel.
  ///
  /// In en, this message translates to:
  /// **'Account'**
  String get accountLabel;

  /// No description provided for @voucherDialogTitle.
  ///
  /// In en, this message translates to:
  /// **'Voucher issued'**
  String get voucherDialogTitle;

  /// No description provided for @voucherCodeLabel.
  ///
  /// In en, this message translates to:
  /// **'Voucher code'**
  String get voucherCodeLabel;

  /// No description provided for @voucherStatusIssued.
  ///
  /// In en, this message translates to:
  /// **'Issued'**
  String get voucherStatusIssued;

  /// No description provided for @voucherStatusFulfilled.
  ///
  /// In en, this message translates to:
  /// **'Fulfilled'**
  String get voucherStatusFulfilled;

  /// No description provided for @voucherStatusCancelled.
  ///
  /// In en, this message translates to:
  /// **'Cancelled'**
  String get voucherStatusCancelled;

  /// No description provided for @pointsSpentLabel.
  ///
  /// In en, this message translates to:
  /// **'Points spent'**
  String get pointsSpentLabel;

  /// No description provided for @statusLabel.
  ///
  /// In en, this message translates to:
  /// **'Status'**
  String get statusLabel;

  /// No description provided for @playerLabel.
  ///
  /// In en, this message translates to:
  /// **'Player'**
  String get playerLabel;

  /// No description provided for @closeButton.
  ///
  /// In en, this message translates to:
  /// **'Close'**
  String get closeButton;

  /// No description provided for @vouchersEmpty.
  ///
  /// In en, this message translates to:
  /// **'No vouchers yet.'**
  String get vouchersEmpty;

  /// No description provided for @staffLoginTitle.
  ///
  /// In en, this message translates to:
  /// **'Staff scanner login'**
  String get staffLoginTitle;

  /// No description provided for @staffLoginSubtitle.
  ///
  /// In en, this message translates to:
  /// **'Use a staff account with scanner access.'**
  String get staffLoginSubtitle;

  /// No description provided for @staffLoginButton.
  ///
  /// In en, this message translates to:
  /// **'Sign in as staff'**
  String get staffLoginButton;

  /// No description provided for @backToParentLogin.
  ///
  /// In en, this message translates to:
  /// **'Back to parent login'**
  String get backToParentLogin;

  /// No description provided for @staffScannerTitle.
  ///
  /// In en, this message translates to:
  /// **'Scanner'**
  String get staffScannerTitle;

  /// No description provided for @fixtureLabel.
  ///
  /// In en, this message translates to:
  /// **'Fixture'**
  String get fixtureLabel;

  /// No description provided for @manualTokenTitle.
  ///
  /// In en, this message translates to:
  /// **'Manual token entry'**
  String get manualTokenTitle;

  /// No description provided for @tokenLabel.
  ///
  /// In en, this message translates to:
  /// **'QR token'**
  String get tokenLabel;

  /// No description provided for @manualTokenHint.
  ///
  /// In en, this message translates to:
  /// **'Paste the parent token here for simulator demos.'**
  String get manualTokenHint;

  /// No description provided for @submitScanButton.
  ///
  /// In en, this message translates to:
  /// **'Submit scan'**
  String get submitScanButton;

  /// No description provided for @hideCameraButton.
  ///
  /// In en, this message translates to:
  /// **'Hide camera'**
  String get hideCameraButton;

  /// No description provided for @openCameraButton.
  ///
  /// In en, this message translates to:
  /// **'Open camera'**
  String get openCameraButton;

  /// No description provided for @scanSuccessTitle.
  ///
  /// In en, this message translates to:
  /// **'Scan credited'**
  String get scanSuccessTitle;

  /// No description provided for @totalPointsLabel.
  ///
  /// In en, this message translates to:
  /// **'Total points'**
  String get totalPointsLabel;

  /// No description provided for @selectFixtureHint.
  ///
  /// In en, this message translates to:
  /// **'Select a fixture first.'**
  String get selectFixtureHint;

  /// No description provided for @staffForbiddenError.
  ///
  /// In en, this message translates to:
  /// **'You do not have scanner access.'**
  String get staffForbiddenError;

  /// No description provided for @scanAlreadyUsedError.
  ///
  /// In en, this message translates to:
  /// **'Already scanned for this match.'**
  String get scanAlreadyUsedError;

  /// No description provided for @scanInvalidQrError.
  ///
  /// In en, this message translates to:
  /// **'The QR token is invalid or expired.'**
  String get scanInvalidQrError;

  /// No description provided for @scanFixtureClosedError.
  ///
  /// In en, this message translates to:
  /// **'This fixture is not open for scanning.'**
  String get scanFixtureClosedError;

  /// No description provided for @scanNoLinkedPlayerError.
  ///
  /// In en, this message translates to:
  /// **'No linked player is on this fixture\'s team.'**
  String get scanNoLinkedPlayerError;

  /// No description provided for @scanValidationError.
  ///
  /// In en, this message translates to:
  /// **'The scan could not be completed.'**
  String get scanValidationError;

  /// No description provided for @themeLabel.
  ///
  /// In en, this message translates to:
  /// **'Theme'**
  String get themeLabel;

  /// No description provided for @memberLabel.
  ///
  /// In en, this message translates to:
  /// **'Member'**
  String get memberLabel;

  /// No description provided for @staffRole.
  ///
  /// In en, this message translates to:
  /// **'Staff'**
  String get staffRole;

  /// No description provided for @loginTagline.
  ///
  /// In en, this message translates to:
  /// **'Your academy. Your points. Your matchday.'**
  String get loginTagline;
}

class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  Future<AppLocalizations> load(Locale locale) {
    return SynchronousFuture<AppLocalizations>(lookupAppLocalizations(locale));
  }

  @override
  bool isSupported(Locale locale) =>
      <String>['ar', 'en'].contains(locale.languageCode);

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}

AppLocalizations lookupAppLocalizations(Locale locale) {
  // Lookup logic when only language code is specified.
  switch (locale.languageCode) {
    case 'ar':
      return AppLocalizationsAr();
    case 'en':
      return AppLocalizationsEn();
  }

  throw FlutterError(
    'AppLocalizations.delegate failed to load unsupported locale "$locale". This is likely '
    'an issue with the localizations generation tool. Please file an issue '
    'on GitHub with a reproducible sample app and the gen-l10n configuration '
    'that was used.',
  );
}
