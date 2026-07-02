import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../l10n/app_localizations.dart';
import 'branding/brand.dart';
import 'providers.dart';
import 'features/auth/presentation/login_screen.dart';
import 'features/home/presentation/home_shell.dart';
import 'features/session/session_controller.dart';
import 'features/staff/presentation/staff_login_screen.dart';
import 'features/staff/presentation/staff_scanner_screen.dart';
import 'features/staff/staff_session_controller.dart';
import 'theme/app_theme.dart';

class CoreApp extends ConsumerWidget {
  const CoreApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final brand = ref.watch(brandProvider);
    final locale = ref.watch(localeControllerProvider);
    final themeMode = ref.watch(themeControllerProvider);
    final session = ref.watch(sessionControllerProvider);
    final staffSession = ref.watch(staffSessionControllerProvider);

    final home = switch (staffSession.status) {
      StaffSessionStatus.authenticated => const StaffScannerScreen(),
      StaffSessionStatus.login => const StaffLoginScreen(),
      StaffSessionStatus.hidden => switch (session.status) {
        SessionStatus.authenticated => const HomeShell(),
        SessionStatus.unauthenticated => const LoginScreen(),
        SessionStatus.unknown => const _SplashScreen(),
      },
    };

    return MaterialApp(
      title: brand.appTitle,
      debugShowCheckedModeBanner: false,
      theme: buildLightTheme(brand.palette),
      darkTheme: buildDarkTheme(brand.palette),
      themeMode: themeMode,
      locale: locale,
      supportedLocales: AppLocalizations.supportedLocales,
      localizationsDelegates: const [
        AppLocalizations.delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      home: home,
    );
  }
}

class _SplashScreen extends StatelessWidget {
  const _SplashScreen();

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(body: Center(child: Text(l10n.loadingText)));
  }
}
