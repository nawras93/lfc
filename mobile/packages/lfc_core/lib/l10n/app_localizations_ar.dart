// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for Arabic (`ar`).
class AppLocalizationsAr extends AppLocalizations {
  AppLocalizationsAr([String locale = 'ar']) : super(locale);

  @override
  String get appTitle => 'أكاديمية لوسيل';

  @override
  String get loginTitle => 'تسجيل الدخول';

  @override
  String get loginSubtitle =>
      'استخدم حساب ولي الأمر أو حساب كبار الشخصيات للمتابعة.';

  @override
  String get emailLabel => 'البريد الإلكتروني';

  @override
  String get passwordLabel => 'كلمة المرور';

  @override
  String get inviteTokenLabel => 'رمز الدعوة';

  @override
  String get signInButton => 'دخول';

  @override
  String get acceptInviteButton => 'قبول الدعوة';

  @override
  String get openAcceptInvite => 'تعيين كلمة المرور من الدعوة';

  @override
  String get backToLogin => 'العودة إلى تسجيل الدخول';

  @override
  String get logoutButton => 'تسجيل الخروج';

  @override
  String get languageToggle => 'EN';

  @override
  String get languageLabel => 'اللغة';

  @override
  String get homeTitle => 'الحساب';

  @override
  String get accountTypeLabel => 'نوع الحساب';

  @override
  String get vvipStatusLabel => 'كبار الشخصيات';

  @override
  String get balanceLabel => 'رصيد الحساب';

  @override
  String get yesText => 'نعم';

  @override
  String get noText => 'لا';

  @override
  String get requiredField => 'هذا الحقل مطلوب.';

  @override
  String get invalidEmail => 'أدخل بريدًا إلكترونيًا صحيحًا.';

  @override
  String get passwordMinLength => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل.';

  @override
  String get sessionExpired => 'انتهت الجلسة. يرجى تسجيل الدخول مرة أخرى.';

  @override
  String get genericError => 'حدث خطأ ما. يرجى المحاولة مرة أخرى.';

  @override
  String get networkError =>
      'تعذر الوصول إلى الخادم. تحقق من عنوان الـ API والاتصال.';

  @override
  String get serverError => 'الخادم غير متاح الآن. حاول مرة أخرى بعد قليل.';

  @override
  String get loadingText => 'جارٍ التحميل...';

  @override
  String welcomeLabel(Object name) {
    return 'مرحبًا، $name';
  }

  @override
  String get acceptInviteTitle => 'قبول الدعوة';

  @override
  String get acceptInviteSubtitle => 'عيّن كلمة مرور لتفعيل حسابك.';

  @override
  String get savePasswordButton => 'حفظ كلمة المرور';

  @override
  String get demoApiHint =>
      'يُضبط عنوان الـ API التجريبي تلقائياً إلى localhost على iOS وإلى 10.0.2.2 على Android. ويمكن تجاوزه عبر --dart-define=API_BASE_URL.';

  @override
  String get notAvailableValue => 'غير متوفر';

  @override
  String get playersTab => 'اللاعبون';

  @override
  String get qrTab => 'الرمز';

  @override
  String get rewardsTab => 'المكافآت';

  @override
  String get offersTab => 'العروض';

  @override
  String get staffScannerEntry => 'ماسح الموظفين';

  @override
  String get pointsUnit => 'نقطة';

  @override
  String get playersEmptyTitle => 'لا يوجد لاعبون مرتبطون بعد';

  @override
  String get playersEmptyBody =>
      'هذا الحساب لا يحتوي على روابط لاعبين في النسخة التجريبية.';

  @override
  String get accountPointsHistoryTitle => 'سجل نقاط الحساب';

  @override
  String playerPointsHistoryTitle(Object name) {
    return 'سجل نقاط $name';
  }

  @override
  String get noTransactionsEmpty => 'لا توجد حركات نقاط بعد.';

  @override
  String get transactionSourceScan => 'مسح الحضور';

  @override
  String get transactionSourceRedemption => 'استبدال';

  @override
  String get transactionTypeEarn => 'اكتساب';

  @override
  String get transactionTypeRedeem => 'استبدال';

  @override
  String get transactionTypeExpire => 'انتهاء';

  @override
  String get transactionTypeAdjust => 'تعديل';

  @override
  String get transactionTypeReverse => 'عكس';

  @override
  String get teamLabel => 'الفريق';

  @override
  String get positionLabel => 'المركز';

  @override
  String get progressLabel => 'التقدم';

  @override
  String get pointsHistoryAction => 'سجل النقاط';

  @override
  String get retryButton => 'إعادة المحاولة';

  @override
  String get qrScreenTitle => 'اعرض هذا الرمز عند البوابة';

  @override
  String get qrScreenSubtitle =>
      'سيقوم الموظف بمسحه لإضافة النقاط للاعبين المؤهلين في المباراة المفتوحة.';

  @override
  String qrExpiresIn(int seconds) {
    return 'يتجدد خلال $secondsث';
  }

  @override
  String get refreshQrButton => 'تحديث الرمز';

  @override
  String get offersEmpty => 'لا توجد عروض متاحة الآن.';

  @override
  String get vvipBadge => 'كبار الشخصيات';

  @override
  String offerValidity(Object from, Object until) {
    return 'صالح من $from حتى $until';
  }

  @override
  String get selectPlayerBeforeRedeem => 'اختر لاعبًا قبل الاستبدال.';

  @override
  String get redeemForPlayerLabel => 'الاستبدال للاعب';

  @override
  String get catalogTab => 'الكتالوج';

  @override
  String get vouchersTab => 'القسائم';

  @override
  String get redeemButton => 'استبدال';

  @override
  String get outOfStockLabel => 'غير متوفر';

  @override
  String get redeemLinkedPlayerError => 'هذا اللاعب غير مرتبط بحسابك.';

  @override
  String get redeemVvipOnlyError =>
      'الاستبدال على مستوى الحساب يتطلب حساب كبار الشخصيات.';

  @override
  String get redeemInsufficientError => 'لا تملك نقاطًا كافية لهذه المكافأة.';

  @override
  String get redeemUnavailableError => 'هذه المكافأة غير متاحة الآن.';

  @override
  String get redemptionTypeFeeDiscount => 'خصم رسوم';

  @override
  String get redemptionTypeEvent => 'فعالية';

  @override
  String get redemptionTypeMerch => 'منتجات';

  @override
  String get accountLabel => 'الحساب';

  @override
  String get voucherDialogTitle => 'تم إصدار القسيمة';

  @override
  String get voucherCodeLabel => 'رمز القسيمة';

  @override
  String get voucherStatusIssued => 'صادرة';

  @override
  String get voucherStatusFulfilled => 'مستوفاة';

  @override
  String get voucherStatusCancelled => 'ملغاة';

  @override
  String get pointsSpentLabel => 'النقاط المصروفة';

  @override
  String get statusLabel => 'الحالة';

  @override
  String get playerLabel => 'اللاعب';

  @override
  String get closeButton => 'إغلاق';

  @override
  String get vouchersEmpty => 'لا توجد قسائم بعد.';

  @override
  String get staffLoginTitle => 'دخول ماسح الموظفين';

  @override
  String get staffLoginSubtitle => 'استخدم حساب موظف يملك صلاحية المسح.';

  @override
  String get staffLoginButton => 'دخول كموظف';

  @override
  String get backToParentLogin => 'العودة إلى دخول أولياء الأمور';

  @override
  String get staffScannerTitle => 'الماسح';

  @override
  String get fixtureLabel => 'المباراة';

  @override
  String get manualTokenTitle => 'إدخال الرمز يدويًا';

  @override
  String get tokenLabel => 'رمز QR';

  @override
  String get manualTokenHint => 'الصق رمز ولي الأمر هنا لعروض المحاكيات.';

  @override
  String get submitScanButton => 'إرسال المسح';

  @override
  String get hideCameraButton => 'إخفاء الكاميرا';

  @override
  String get openCameraButton => 'فتح الكاميرا';

  @override
  String get scanSuccessTitle => 'تم احتساب المسح';

  @override
  String get totalPointsLabel => 'إجمالي النقاط';

  @override
  String get selectFixtureHint => 'اختر مباراة أولًا.';

  @override
  String get staffForbiddenError => 'ليست لديك صلاحية الماسح.';

  @override
  String get scanAlreadyUsedError => 'تم المسح لهذه المباراة بالفعل.';

  @override
  String get scanInvalidQrError => 'رمز QR غير صالح أو منتهي الصلاحية.';

  @override
  String get scanFixtureClosedError => 'هذه المباراة غير مفتوحة للمسح.';

  @override
  String get scanNoLinkedPlayerError =>
      'لا يوجد لاعب مرتبط في فريق هذه المباراة.';

  @override
  String get scanValidationError => 'تعذر إكمال عملية المسح.';

  @override
  String get themeLabel => 'المظهر';

  @override
  String get memberLabel => 'عضو';

  @override
  String get staffRole => 'موظف';

  @override
  String get loginTagline => 'أكاديميتك. نقاطك. يوم مباراتك.';

  @override
  String get homeNavLabel => 'الرئيسية';

  @override
  String get matchesNavLabel => 'المباريات';

  @override
  String get membershipNavLabel => 'العضوية';

  @override
  String get latestNewsTitle => 'آخر الأخبار';

  @override
  String get fixturesTitle => 'المباريات القادمة';

  @override
  String get resultsTitle => 'النتائج';

  @override
  String get tableTitle => 'الترتيب';

  @override
  String get newsEmptyState => 'لا توجد أخبار منشورة بعد.';

  @override
  String get fixturesEmptyState => 'لا توجد مباريات قادمة الآن.';

  @override
  String get resultsEmptyState => 'لا توجد نتائج متاحة بعد.';

  @override
  String get tableEmptyState => 'جدول الترتيب غير متاح بعد.';

  @override
  String get membershipSignInPrompt => 'سجّل الدخول للوصول إلى عضويتك';

  @override
  String get membershipComingSoon => 'عضويتك - قريبًا';

  @override
  String get clubColumnLabel => 'النادي';

  @override
  String get positionColumnShort => 'م';

  @override
  String get playedColumnShort => 'ل';

  @override
  String get wonColumnShort => 'ف';

  @override
  String get drawnColumnShort => 'ت';

  @override
  String get lostColumnShort => 'خ';

  @override
  String get goalDifferenceColumnShort => 'ف.أ';

  @override
  String get pointsColumnShort => 'ن';

  @override
  String get venueLabel => 'الملعب';

  @override
  String get homeMatchChip => 'داخل الأرض';

  @override
  String get awayMatchChip => 'خارج الأرض';
}
