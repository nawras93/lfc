/// The app identifiers the API scopes data by — mirrors PHP's `App\Enums\AppKey`.
///
/// Sent as the `X-App-Key` header (see [AppConfig.appKey]). Use these constants rather
/// than a raw string: a typo compiles fine but is unparseable server-side, and the
/// client is then quietly served another app's data.
class AppKeys {
  const AppKeys._();

  static const String appOne = 'app_one';
  static const String appTwo = 'app_two';
}
