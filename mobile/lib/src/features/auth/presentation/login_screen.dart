import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../core/api/api_exception.dart';
import '../../../providers.dart';
import 'accept_invite_screen.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  ApiException? _error;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() => _error = null);

    try {
      await ref.read(sessionControllerProvider.notifier).login(
            email: _emailController.text.trim(),
            password: _passwordController.text,
          );
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

    return Scaffold(
      appBar: AppBar(
        title: Text(l10n.loginTitle),
        actions: const [_LanguageToggleButton()],
      ),
      body: SafeArea(
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 480),
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Card(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Text(
                          l10n.loginSubtitle,
                          style: Theme.of(context).textTheme.bodyLarge,
                        ),
                        const SizedBox(height: 24),
                        TextFormField(
                          key: const Key('login-email'),
                          controller: _emailController,
                          keyboardType: TextInputType.emailAddress,
                          decoration: InputDecoration(
                            labelText: l10n.emailLabel,
                            errorText: _error?.firstErrorFor('email'),
                          ),
                          validator: (value) {
                            final text = value?.trim() ?? '';
                            if (text.isEmpty) {
                              return l10n.requiredField;
                            }
                            final emailPattern = RegExp(r'^[^@\\s]+@[^@\\s]+\\.[^@\\s]+\$');
                            if (!emailPattern.hasMatch(text)) {
                              return l10n.invalidEmail;
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 16),
                        TextFormField(
                          key: const Key('login-password'),
                          controller: _passwordController,
                          obscureText: true,
                          decoration: InputDecoration(
                            labelText: l10n.passwordLabel,
                            errorText: _error?.firstErrorFor('password'),
                          ),
                          validator: (value) {
                            if ((value ?? '').isEmpty) {
                              return l10n.requiredField;
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 12),
                        if (_error != null && _error!.kind != ApiErrorKind.validation)
                          Padding(
                            padding: const EdgeInsets.only(bottom: 12),
                            child: Text(
                              _friendlyErrorMessage(l10n, _error!),
                              style: TextStyle(color: Theme.of(context).colorScheme.error),
                            ),
                          ),
                        FilledButton(
                          key: const Key('login-submit'),
                          onPressed: session.isBusy ? null : _submit,
                          child: Text(l10n.signInButton),
                        ),
                        const SizedBox(height: 12),
                        TextButton(
                          onPressed: () {
                            Navigator.of(context).push(
                              MaterialPageRoute<void>(
                                builder: (_) => const AcceptInviteScreen(),
                              ),
                            );
                          },
                          child: Text(l10n.openAcceptInvite),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          l10n.demoApiHint,
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
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

class _LanguageToggleButton extends ConsumerWidget {
  const _LanguageToggleButton();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;

    return IconButton(
      key: const Key('language-toggle'),
      tooltip: l10n.languageLabel,
      onPressed: () => ref.read(localeControllerProvider.notifier).toggle(),
      icon: Text(
        l10n.languageToggle,
        style: Theme.of(context).textTheme.labelLarge,
      ),
    );
  }
}
