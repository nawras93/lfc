import 'package:flutter/material.dart';

/// Lusail Football Academy brand palette.
///
/// Navy is the club's own crest colour (#113F71 — sampled from the Lusail SC
/// sidra-frond emblem). Gold echoes Lusail Stadium's burnished "golden vessel"
/// facade and its triangular fanar-lamp panelling (the app's diagrid motif),
/// and pairs with the navy as the premium / VVIP accent.
class LfcColors {
  LfcColors._();

  // Navy scale (from the Lusail SC crest, #113F71)
  static const navy900 = Color(0xFF06223C);
  static const navy800 = Color(0xFF0B3059);
  static const navy700 = Color(0xFF113F71); // brand primary (crest navy)
  static const navy600 = Color(0xFF1C5288);
  static const navy500 = Color(0xFF2E659B);
  static const navy300 = Color(0xFF8FB2CF);
  static const navy100 = Color(0xFFD4E3F0);

  // Lusail gold
  static const goldDeep = Color(0xFF9A7526);
  static const gold = Color(0xFFC8A24A);
  static const goldBright = Color(0xFFE7C97C);

  // Dark neutrals — cool, navy-tinted charcoal
  static const darkBg = Color(0xFF081018);
  static const darkSurface = Color(0xFF0E1A28);
  static const darkSurfaceHi = Color(0xFF16273A);
  static const darkOutline = Color(0xFF2B3F57);
  static const darkText = Color(0xFFEAF1F8);
  static const darkTextMuted = Color(0xFFA6B9CD);

  // Light neutrals — cool off-white
  static const lightBg = Color(0xFFF3F7FB);
  static const lightSurface = Color(0xFFFFFFFF);
  static const lightSurfaceHi = Color(0xFFE9F0F7);
  static const lightOutline = Color(0xFFDCE7F1);
  static const lightText = Color(0xFF0D1E30);
  static const lightTextMuted = Color(0xFF5A6E84);

  static const success = Color(0xFF2E7D5B);
  static const successBright = Color(0xFF7FD0A8);
}

/// Brand accents that live outside the Material [ColorScheme] — the gold tier
/// colour, the maroon "matchday" hero gradient, and the fanar pattern tint.
@immutable
class LfcPalette extends ThemeExtension<LfcPalette> {
  const LfcPalette({
    required this.gold,
    required this.goldBright,
    required this.onHero,
    required this.onHeroMuted,
    required this.heroGradient,
    required this.heroPattern,
    required this.success,
    required this.muted,
  });

  final Color gold;
  final Color goldBright;

  /// Text/icon colour on the navy hero surface.
  final Color onHero;
  final Color onHeroMuted;

  /// Diagonal gradient for the "matchday" hero card (stays navy in both modes).
  final List<Color> heroGradient;

  /// Line colour of the fanar diagrid drawn over the hero.
  final Color heroPattern;

  final Color success;
  final Color muted;

  static final LfcPalette dark = LfcPalette(
    gold: LfcColors.goldBright,
    goldBright: LfcColors.goldBright,
    onHero: const Color(0xFFEAF2FA),
    onHeroMuted: const Color(0xFFB6CBE0),
    heroGradient: const [Color(0xFF0A2A4B), LfcColors.navy700],
    heroPattern: LfcColors.goldBright.withValues(alpha: 0.14),
    success: LfcColors.successBright,
    muted: LfcColors.darkTextMuted,
  );

  static final LfcPalette light = LfcPalette(
    gold: LfcColors.gold,
    goldBright: LfcColors.goldBright,
    onHero: const Color(0xFFEAF2FA),
    onHeroMuted: const Color(0xFFBFD3E6),
    heroGradient: const [LfcColors.navy700, LfcColors.navy600],
    heroPattern: LfcColors.goldBright.withValues(alpha: 0.18),
    success: LfcColors.success,
    muted: LfcColors.lightTextMuted,
  );

  @override
  LfcPalette copyWith({
    Color? gold,
    Color? goldBright,
    Color? onHero,
    Color? onHeroMuted,
    List<Color>? heroGradient,
    Color? heroPattern,
    Color? success,
    Color? muted,
  }) {
    return LfcPalette(
      gold: gold ?? this.gold,
      goldBright: goldBright ?? this.goldBright,
      onHero: onHero ?? this.onHero,
      onHeroMuted: onHeroMuted ?? this.onHeroMuted,
      heroGradient: heroGradient ?? this.heroGradient,
      heroPattern: heroPattern ?? this.heroPattern,
      success: success ?? this.success,
      muted: muted ?? this.muted,
    );
  }

  @override
  LfcPalette lerp(ThemeExtension<LfcPalette>? other, double t) {
    if (other is! LfcPalette) {
      return this;
    }
    return LfcPalette(
      gold: Color.lerp(gold, other.gold, t)!,
      goldBright: Color.lerp(goldBright, other.goldBright, t)!,
      onHero: Color.lerp(onHero, other.onHero, t)!,
      onHeroMuted: Color.lerp(onHeroMuted, other.onHeroMuted, t)!,
      heroGradient: [
        for (var i = 0; i < heroGradient.length; i++)
          Color.lerp(heroGradient[i], other.heroGradient[i], t)!,
      ],
      heroPattern: Color.lerp(heroPattern, other.heroPattern, t)!,
      success: Color.lerp(success, other.success, t)!,
      muted: Color.lerp(muted, other.muted, t)!,
    );
  }
}

/// Convenience accessor for the brand palette on the current theme.
extension LfcPaletteContext on BuildContext {
  LfcPalette get lfc =>
      Theme.of(this).extension<LfcPalette>() ?? LfcPalette.light;
}

const _displayFont = 'Changa';
const _bodyFont = 'Tajawal';

ThemeData buildLightTheme() => _buildTheme(_lightScheme, LfcPalette.light);

ThemeData buildDarkTheme() => _buildTheme(_darkScheme, LfcPalette.dark);

const ColorScheme _lightScheme = ColorScheme(
  brightness: Brightness.light,
  primary: LfcColors.navy700,
  onPrimary: Colors.white,
  primaryContainer: LfcColors.navy100,
  onPrimaryContainer: LfcColors.navy900,
  secondary: LfcColors.goldDeep,
  onSecondary: Colors.white,
  secondaryContainer: Color(0xFFF6E7C2),
  onSecondaryContainer: Color(0xFF3D2D00),
  tertiary: LfcColors.navy600,
  onTertiary: Colors.white,
  error: Color(0xFFB3261E),
  onError: Colors.white,
  errorContainer: Color(0xFFF9DEDC),
  onErrorContainer: Color(0xFF410E0B),
  surface: LfcColors.lightSurface,
  onSurface: LfcColors.lightText,
  onSurfaceVariant: LfcColors.lightTextMuted,
  surfaceContainerLowest: Colors.white,
  surfaceContainerLow: Color(0xFFF6F9FC),
  surfaceContainer: Color(0xFFEFF4F9),
  surfaceContainerHigh: Color(0xFFEAF1F7),
  surfaceContainerHighest: LfcColors.lightSurfaceHi,
  surfaceDim: Color(0xFFD9E3EE),
  surfaceBright: Colors.white,
  outline: Color(0xFFC4D3E1),
  outlineVariant: Color(0xFFE1EAF2),
  inverseSurface: Color(0xFF22384C),
  onInverseSurface: Color(0xFFEDF3F9),
  inversePrimary: LfcColors.navy300,
  shadow: Colors.black,
  scrim: Colors.black,
);

const ColorScheme _darkScheme = ColorScheme(
  brightness: Brightness.dark,
  primary: LfcColors.navy500,
  onPrimary: Colors.white,
  primaryContainer: LfcColors.navy800,
  onPrimaryContainer: LfcColors.navy100,
  secondary: LfcColors.gold,
  onSecondary: Color(0xFF2A1D00),
  secondaryContainer: LfcColors.goldDeep,
  onSecondaryContainer: Color(0xFFFFF2D6),
  tertiary: LfcColors.goldBright,
  onTertiary: Color(0xFF2A1D00),
  error: Color(0xFFFFB4AB),
  onError: Color(0xFF690005),
  errorContainer: Color(0xFF93000A),
  onErrorContainer: Color(0xFFFFDAD6),
  surface: LfcColors.darkSurface,
  onSurface: LfcColors.darkText,
  onSurfaceVariant: LfcColors.darkTextMuted,
  surfaceContainerLowest: Color(0xFF070D14),
  surfaceContainerLow: Color(0xFF0D1722),
  surfaceContainer: Color(0xFF11202F),
  surfaceContainerHigh: Color(0xFF17293C),
  surfaceContainerHighest: LfcColors.darkSurfaceHi,
  surfaceDim: LfcColors.darkBg,
  surfaceBright: Color(0xFF1D3145),
  outline: LfcColors.darkOutline,
  outlineVariant: Color(0xFF243547),
  inverseSurface: Color(0xFFEAF1F8),
  onInverseSurface: Color(0xFF1B2C3D),
  inversePrimary: LfcColors.navy700,
  shadow: Colors.black,
  scrim: Colors.black,
);

ThemeData _buildTheme(ColorScheme scheme, LfcPalette palette) {
  final isDark = scheme.brightness == Brightness.dark;
  final scaffoldBg = isDark ? LfcColors.darkBg : LfcColors.lightBg;
  final textTheme = _buildTextTheme(scheme.onSurface, palette.muted);

  final inputDecorationTheme = InputDecorationTheme(
    filled: true,
    fillColor: scheme.surfaceContainerHighest,
    alignLabelWithHint: true,
    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: BorderSide.none,
    ),
    enabledBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: BorderSide.none,
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: BorderSide(color: scheme.primary, width: 1.6),
    ),
  );

  return ThemeData(
    useMaterial3: true,
    colorScheme: scheme,
    scaffoldBackgroundColor: scaffoldBg,
    fontFamily: _bodyFont,
    textTheme: textTheme,
    extensions: [palette],
    splashFactory: InkSparkle.splashFactory,
    appBarTheme: AppBarTheme(
      backgroundColor: scaffoldBg,
      surfaceTintColor: Colors.transparent,
      foregroundColor: scheme.onSurface,
      centerTitle: false,
      elevation: 0,
      scrolledUnderElevation: 0,
      titleTextStyle: textTheme.titleLarge,
    ),
    cardTheme: CardThemeData(
      margin: EdgeInsets.zero,
      elevation: 0,
      color: isDark ? scheme.surfaceContainer : scheme.surface,
      surfaceTintColor: Colors.transparent,
      clipBehavior: Clip.antiAlias,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(20),
        side: BorderSide(color: scheme.outlineVariant),
      ),
    ),
    dividerTheme: DividerThemeData(
      color: scheme.outlineVariant,
      thickness: 1,
      space: 1,
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        minimumSize: const Size.fromHeight(52),
        backgroundColor: scheme.primary,
        foregroundColor: scheme.onPrimary,
        disabledBackgroundColor: scheme.onSurface.withValues(alpha: 0.12),
        disabledForegroundColor: scheme.onSurface.withValues(alpha: 0.38),
        textStyle: const TextStyle(
          fontFamily: _displayFont,
          fontWeight: FontWeight.w600,
          fontSize: 16,
          letterSpacing: 0.3,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
        ),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        minimumSize: const Size.fromHeight(52),
        foregroundColor: scheme.onSurface,
        side: BorderSide(color: scheme.outline),
        textStyle: const TextStyle(
          fontFamily: _displayFont,
          fontWeight: FontWeight.w600,
          fontSize: 16,
          letterSpacing: 0.3,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
        ),
      ),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: isDark ? palette.goldBright : LfcColors.goldDeep,
        textStyle: textTheme.labelLarge,
      ),
    ),
    inputDecorationTheme: inputDecorationTheme,
    dropdownMenuTheme: DropdownMenuThemeData(
      inputDecorationTheme: inputDecorationTheme,
      textStyle: textTheme.bodyLarge,
      menuStyle: MenuStyle(
        backgroundColor: WidgetStatePropertyAll(scheme.surfaceContainerHigh),
        surfaceTintColor: const WidgetStatePropertyAll(Colors.transparent),
        elevation: const WidgetStatePropertyAll(3),
        shadowColor: const WidgetStatePropertyAll(Colors.black),
        padding: const WidgetStatePropertyAll(
          EdgeInsets.symmetric(vertical: 6),
        ),
        shape: WidgetStatePropertyAll(
          RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: BorderSide(color: scheme.outlineVariant),
          ),
        ),
      ),
    ),
    chipTheme: ChipThemeData(
      backgroundColor: scheme.surfaceContainerHighest,
      side: BorderSide.none,
      labelStyle: textTheme.labelMedium,
      shape: const StadiumBorder(),
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
    ),
    navigationBarTheme: NavigationBarThemeData(
      backgroundColor: isDark ? scheme.surfaceContainerLow : scheme.surface,
      surfaceTintColor: Colors.transparent,
      indicatorColor: scheme.primary.withValues(alpha: isDark ? 0.32 : 0.14),
      elevation: 0,
      height: 68,
      labelTextStyle: WidgetStateProperty.resolveWith((states) {
        final selected = states.contains(WidgetState.selected);
        return TextStyle(
          fontFamily: _bodyFont,
          fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
          fontSize: 11.5,
          letterSpacing: 0.2,
          color: selected ? scheme.primary : scheme.onSurfaceVariant,
        );
      }),
      iconTheme: WidgetStateProperty.resolveWith((states) {
        final selected = states.contains(WidgetState.selected);
        return IconThemeData(
          size: 24,
          color: selected ? scheme.primary : scheme.onSurfaceVariant,
        );
      }),
    ),
    tabBarTheme: TabBarThemeData(
      labelColor: scheme.primary,
      unselectedLabelColor: scheme.onSurfaceVariant,
      indicatorColor: palette.gold,
      indicatorSize: TabBarIndicatorSize.label,
      dividerColor: scheme.outlineVariant,
      labelStyle: textTheme.titleSmall,
      unselectedLabelStyle: textTheme.titleSmall,
    ),
    dialogTheme: DialogThemeData(
      backgroundColor: scheme.surface,
      surfaceTintColor: Colors.transparent,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(24),
      ),
      titleTextStyle: textTheme.headlineSmall,
    ),
    snackBarTheme: SnackBarThemeData(
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
      ),
    ),
    listTileTheme: ListTileThemeData(
      iconColor: scheme.onSurfaceVariant,
      titleTextStyle: textTheme.titleMedium,
      subtitleTextStyle: textTheme.bodySmall,
    ),
    progressIndicatorTheme: ProgressIndicatorThemeData(
      color: scheme.primary,
    ),
  );
}

TextTheme _buildTextTheme(Color onSurface, Color muted) {
  const d = _displayFont;
  const b = _bodyFont;
  return TextTheme(
    displayLarge: TextStyle(
      fontFamily: d,
      fontWeight: FontWeight.w700,
      fontSize: 52,
      height: 1.0,
      letterSpacing: -1,
      color: onSurface,
    ),
    displayMedium: TextStyle(
      fontFamily: d,
      fontWeight: FontWeight.w700,
      fontSize: 40,
      height: 1.02,
      letterSpacing: -0.5,
      color: onSurface,
    ),
    displaySmall: TextStyle(
      fontFamily: d,
      fontWeight: FontWeight.w600,
      fontSize: 32,
      color: onSurface,
    ),
    headlineLarge: TextStyle(
      fontFamily: d,
      fontWeight: FontWeight.w700,
      fontSize: 28,
      color: onSurface,
    ),
    headlineMedium: TextStyle(
      fontFamily: d,
      fontWeight: FontWeight.w600,
      fontSize: 24,
      color: onSurface,
    ),
    headlineSmall: TextStyle(
      fontFamily: d,
      fontWeight: FontWeight.w600,
      fontSize: 20,
      letterSpacing: 0.2,
      color: onSurface,
    ),
    titleLarge: TextStyle(
      fontFamily: d,
      fontWeight: FontWeight.w600,
      fontSize: 19,
      color: onSurface,
    ),
    titleMedium: TextStyle(
      fontFamily: b,
      fontWeight: FontWeight.w700,
      fontSize: 16,
      color: onSurface,
    ),
    titleSmall: TextStyle(
      fontFamily: b,
      fontWeight: FontWeight.w700,
      fontSize: 14,
      letterSpacing: 0.1,
      color: onSurface,
    ),
    bodyLarge: TextStyle(
      fontFamily: b,
      fontWeight: FontWeight.w400,
      fontSize: 16,
      height: 1.4,
      color: onSurface,
    ),
    bodyMedium: TextStyle(
      fontFamily: b,
      fontWeight: FontWeight.w400,
      fontSize: 14,
      height: 1.4,
      color: onSurface,
    ),
    bodySmall: TextStyle(
      fontFamily: b,
      fontWeight: FontWeight.w400,
      fontSize: 12.5,
      height: 1.35,
      color: muted,
    ),
    labelLarge: TextStyle(
      fontFamily: b,
      fontWeight: FontWeight.w700,
      fontSize: 14,
      letterSpacing: 0.3,
      color: onSurface,
    ),
    labelMedium: TextStyle(
      fontFamily: b,
      fontWeight: FontWeight.w600,
      fontSize: 12,
      letterSpacing: 0.4,
      color: muted,
    ),
    labelSmall: TextStyle(
      fontFamily: b,
      fontWeight: FontWeight.w600,
      fontSize: 11,
      letterSpacing: 0.6,
      color: muted,
    ),
  );
}
