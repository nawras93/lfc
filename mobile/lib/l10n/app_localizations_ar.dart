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
      'يتم ضبط عنوان الـ API التجريبي عبر --dart-define=API_BASE_URL.';

  @override
  String get notAvailableValue => 'غير متوفر';
}
