import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

@immutable
class Brand {
  const Brand({
    required this.appTitle,
    required this.palette,
    required this.wordmark,
    required this.logo,
    this.heroLogo,
  });

  final String appTitle;
  final BrandPalette palette;
  final BrandWordmark wordmark;
  final ImageProvider<Object> logo;
  final ImageProvider<Object>? heroLogo;
}

@immutable
class BrandWordmark {
  const BrandWordmark({required this.title, this.subtitle});

  final String title;
  final String? subtitle;
}

@immutable
class BrandPalette {
  const BrandPalette({
    required this.primary,
    required this.primaryMid,
    required this.primaryOnDark,
    required this.primaryStrong,
    required this.primarySoft,
    required this.primaryTint,
    required this.gold,
    required this.goldDeep,
    required this.goldBright,
    required this.heroStart,
    required this.heroEnd,
    required this.success,
    required this.successBright,
  });

  static const lusail = BrandPalette(
    primary: Color(0xFF113F71),
    primaryMid: Color(0xFF1C5288),
    primaryOnDark: Color(0xFF2E659B),
    primaryStrong: Color(0xFF0B3059),
    primarySoft: Color(0xFF8FB2CF),
    primaryTint: Color(0xFFD4E3F0),
    gold: Color(0xFFC8A24A),
    goldDeep: Color(0xFF9A7526),
    goldBright: Color(0xFFE7C97C),
    heroStart: Color(0xFF0A2A4B),
    heroEnd: Color(0xFF113F71),
    success: Color(0xFF2E7D5B),
    successBright: Color(0xFF7FD0A8),
  );

  final Color primary;
  final Color primaryMid;
  final Color primaryOnDark;
  final Color primaryStrong;
  final Color primarySoft;
  final Color primaryTint;
  final Color gold;
  final Color goldDeep;
  final Color goldBright;
  final Color heroStart;
  final Color heroEnd;
  final Color success;
  final Color successBright;
}

final brandProvider = Provider<Brand>(
  (ref) => throw UnimplementedError('override brandProvider per app'),
);
