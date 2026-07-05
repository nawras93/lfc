import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../branding/brand.dart';
import '../../../core/api/api_exception.dart';
import '../../../providers.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/presentation/theme_toggle_button.dart';
import '../../../theme/widgets/fanar_backdrop.dart';
import '../../locale/presentation/language_toggle_button.dart';

enum _AuthMode { signIn, createAccount }

class MemberAuthScreen extends ConsumerStatefulWidget {
  const MemberAuthScreen({super.key});

  @override
  ConsumerState<MemberAuthScreen> createState() => _MemberAuthScreenState();
}

class _MemberAuthScreenState extends ConsumerState<MemberAuthScreen> {
  _AuthMode _mode = _AuthMode.signIn;
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _nameController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _phoneController = TextEditingController();

  ApiException? _error;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _nameController.dispose();
    _confirmPasswordController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() => _error = null);

    final notifier = ref.read(sessionControllerProvider.notifier);

    try {
      if (_mode == _AuthMode.signIn) {
        await notifier.login(
          email: _emailController.text.trim(),
          password: _passwordController.text,
        );
      } else {
        await notifier.register(
          name: _nameController.text.trim(),
          email: _emailController.text.trim(),
          password: _passwordController.text,
          passwordConfirmation: _confirmPasswordController.text,
          phone: _phoneController.text.trim(),
        );
      }
    } on ApiException catch (error) {
      if (mounted) {
        setState(() => _error = error);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final session = ref.watch(sessionControllerProvider);
    final isSignIn = _mode == _AuthMode.signIn;

    return Scaffold(
      body: SafeArea(
        bottom: false,
        child: SingleChildScrollView(
          child: Column(
            children: [
              _Header(tagline: l10n.loginTagline),
              Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 480),
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(20, 24, 20, 24),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          SegmentedButton<_AuthMode>(
                            segments: [
                              ButtonSegment(
                                value: _AuthMode.signIn,
                                label: Text(l10n.signInButton),
                              ),
                              ButtonSegment(
                                value: _AuthMode.createAccount,
                                label: Text(l10n.createAccountButton),
                              ),
                            ],
                            selected: {_mode},
                            onSelectionChanged: (selected) {
                              setState(() {
                                _mode = selected.first;
                                _error = null;
                              });
                            },
                          ),
                          const SizedBox(height: 24),
                          if (!isSignIn) ...[
                            TextFormField(
                              key: const Key('register-name'),
                              controller: _nameController,
                              textCapitalization: TextCapitalization.words,
                              decoration: InputDecoration(
                                labelText: l10n.nameLabel,
                                prefixIcon: const Icon(Icons.person_outline),
                                errorText: _error?.firstErrorFor('name'),
                              ),
                              validator: (value) {
                                if ((value ?? '').trim().isEmpty) {
                                  return l10n.requiredField;
                                }
                                return null;
                              },
                            ),
                            const SizedBox(height: 14),
                          ],
                          TextFormField(
                            key: Key(isSignIn ? 'login-email' : 'register-email'),
                            controller: _emailController,
                            keyboardType: TextInputType.emailAddress,
                            decoration: InputDecoration(
                              labelText: l10n.emailLabel,
                              prefixIcon: const Icon(Icons.alternate_email),
                              errorText: _error?.firstErrorFor('email'),
                            ),
                            validator: (value) {
                              final text = value?.trim() ?? '';
                              if (text.isEmpty) {
                                return l10n.requiredField;
                              }
                              final emailPattern = RegExp(
                                r'^[^@\s]+@[^@\s]+\.[^@\s]+$',
                              );
                              if (!emailPattern.hasMatch(text)) {
                                return l10n.invalidEmail;
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 14),
                          TextFormField(
                            key: Key(isSignIn ? 'login-password' : 'register-password'),
                            controller: _passwordController,
                            obscureText: true,
                            decoration: InputDecoration(
                              labelText: l10n.passwordLabel,
                              prefixIcon: const Icon(Icons.lock_outline),
                              errorText: _error?.firstErrorFor('password'),
                            ),
                            validator: (value) {
                              if ((value ?? '').isEmpty) {
                                return l10n.requiredField;
                              }
                              if ((value ?? '').length < 8) {
                                return l10n.passwordMinLength;
                              }
                              return null;
                            },
                          ),
                          if (!isSignIn) ...[
                            const SizedBox(height: 14),
                            TextFormField(
                              key: const Key('register-confirm-password'),
                              controller: _confirmPasswordController,
                              obscureText: true,
                              decoration: InputDecoration(
                                labelText: l10n.confirmPasswordLabel,
                                prefixIcon: const Icon(Icons.lock_outline),
                                errorText: _error?.firstErrorFor('password'),
                              ),
                              validator: (value) {
                                if (value != _passwordController.text) {
                                  return l10n.passwordsDoNotMatch;
                                }
                                return null;
                              },
                            ),
                            const SizedBox(height: 14),
                            TextFormField(
                              key: const Key('register-phone'),
                              controller: _phoneController,
                              keyboardType: TextInputType.phone,
                              decoration: InputDecoration(
                                labelText: l10n.phoneLabel,
                                prefixIcon: const Icon(Icons.phone_outlined),
                                errorText: _error?.firstErrorFor('phone'),
                              ),
                            ),
                          ],
                          if (!isSignIn) const SizedBox(height: 14),
                          if (_error != null &&
                              _error!.kind != ApiErrorKind.validation)
                            _ErrorBanner(
                              message: _friendlyErrorMessage(l10n, _error!),
                            ),
                          FilledButton(
                            key: const Key('member-auth-submit'),
                            onPressed: session.isBusy ? null : _submit,
                            child: session.isBusy
                                ? const SizedBox(
                                    height: 20,
                                    width: 20,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2.4,
                                      color: Colors.white,
                                    ),
                                  )
                                : Text(
                                    isSignIn
                                        ? l10n.signInButton
                                        : l10n.createAccountButton,
                                  ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _friendlyErrorMessage(AppLocalizations l10n, ApiException error) {
    return switch (error.kind) {
      ApiErrorKind.network => l10n.networkError,
      ApiErrorKind.server => l10n.serverError,
      ApiErrorKind.unauthorized => l10n.sessionExpired,
      _ => error.message.isEmpty ? l10n.genericError : error.message,
    };
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.tagline});

  final String tagline;

  @override
  Widget build(BuildContext context) {
    final brand = ProviderScope.containerOf(context).read(brandProvider);
    final palette = context.lfc;
    final heroLogo = brand.heroLogo ?? brand.logo;

    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: palette.heroGradient,
        ),
        borderRadius: const BorderRadius.vertical(bottom: Radius.circular(28)),
      ),
      child: ClipRRect(
        borderRadius: const BorderRadius.vertical(bottom: Radius.circular(28)),
        child: Stack(
          children: [
            FanarBackdrop(color: palette.heroPattern),
            Column(
              children: [
                const Padding(
                  padding: EdgeInsets.fromLTRB(8, 4, 8, 0),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [ThemeToggleButton(), LanguageToggleButton()],
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.fromLTRB(24, 4, 24, 32),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      ColorFiltered(
                        colorFilter: ColorFilter.mode(
                          palette.onHero,
                          BlendMode.srcIn,
                        ),
                        child: Image(
                          image: heroLogo,
                          height: 132,
                          filterQuality: FilterQuality.medium,
                        ),
                      ),
                      const SizedBox(height: 18),
                      Text(
                        tagline,
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontFamily: 'Changa',
                          fontWeight: FontWeight.w600,
                          fontSize: 20,
                          height: 1.25,
                          color: palette.onHero,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _ErrorBanner extends StatelessWidget {
  const _ErrorBanner({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: scheme.errorContainer,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Icon(Icons.error_outline, size: 18, color: scheme.onErrorContainer),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: TextStyle(color: scheme.onErrorContainer),
            ),
          ),
        ],
      ),
    );
  }
}
