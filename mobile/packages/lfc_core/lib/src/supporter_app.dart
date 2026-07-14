import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../l10n/app_localizations.dart';
import 'branding/brand.dart';
import 'features/staff/presentation/staff_login_screen.dart';
import 'features/staff/presentation/staff_scanner_screen.dart';
import 'features/staff/staff_session_controller.dart';
import 'features/content/presentation/supporter_shell.dart';
import 'providers.dart';
import 'theme/app_theme.dart';

class SupporterApp extends ConsumerWidget {
  const SupporterApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final brand = ref.watch(brandProvider);
    final locale = ref.watch(localeControllerProvider);
    final themeMode = ref.watch(themeControllerProvider);
    final staffSession = ref.watch(staffSessionControllerProvider);

    final home = switch (staffSession.status) {
      StaffSessionStatus.authenticated => const StaffScannerScreen(),
      StaffSessionStatus.login => const StaffLoginScreen(),
      StaffSessionStatus.hidden => const SupporterShell(),
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
