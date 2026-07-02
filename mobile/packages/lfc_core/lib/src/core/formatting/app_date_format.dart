import 'package:intl/intl.dart';

/// Centralized date formats for the app. Dates render as dd-MM-yyyy everywhere
/// (matching the admin panel); the active locale is passed through so digits
/// follow the UI language.
class AppDateFormat {
  const AppDateFormat._();

  /// dd-MM-yyyy — a plain date.
  static DateFormat date([String? locale]) => DateFormat('dd-MM-yyyy', locale);

  /// dd-MM-yyyy HH:mm — a date with time.
  static DateFormat dateTime([String? locale]) =>
      DateFormat('dd-MM-yyyy HH:mm', locale);
}
