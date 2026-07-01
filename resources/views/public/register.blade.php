<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('public-registration.meta.title') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        @include('public.partials.register-styles')
    </head>
    <body class="lfc-page">
        <div class="lfc-shell">
            <header class="lfc-topbar">
                <a href="{{ route('public.home') }}" aria-label="{{ __('public-registration.hero.eyebrow') }}">
                    <img class="lfc-brand-logo" src="{{ asset('images/lusail-logo-light.png') }}" alt="Lusail SC">
                </a>

                <div class="lfc-topbar-actions">
                    @if ($season)
                        <span class="lfc-season">{{ __('public-registration.nav.season') }}: {{ $season->name }}</span>
                    @endif
                    @php
                        // Show the target language's flag, like the mobile toggle:
                        // Qatar flag (→ Arabic) while in English, GB flag (→ English) while in Arabic.
                        $targetLang = $locale === 'ar' ? 'en' : 'ar';
                        $targetFlag = $locale === 'ar' ? 'gb' : 'qa';
                        $targetLabel = $locale === 'ar' ? 'English' : 'العربية';
                    @endphp
                    {{-- Stay on the current page; just flip the lang query param. --}}
                    <a class="lfc-language-switch"
                        href="{{ request()->fullUrlWithQuery(['lang' => $targetLang]) }}"
                        aria-label="{{ __('public-registration.nav.language') }}: {{ $targetLabel }}"
                        title="{{ $targetLabel }}">
                        <img class="lfc-flag" src="{{ asset('images/flags/' . $targetFlag . '.png') }}" alt="{{ $targetLabel }}">
                    </a>
                </div>
            </header>

            <main class="lfc-grid">
                <section class="lfc-hero-card">
                    <p class="lfc-hero-kicker">{{ __('public-registration.hero.eyebrow') }}</p>
                    <h2>{{ __('public-registration.hero.title') }}</h2>
                    <p class="lfc-hero-copy">{{ __('public-registration.hero.body') }}</p>

                    @if ($season && $registrationOpen)
                        <div class="lfc-hero-actions">
                            <a href="#registration-form" class="lfc-button lfc-button-primary">{{ __('public-registration.hero.primary_cta') }}</a>
                            <a href="#registration-form" class="lfc-button lfc-button-secondary">{{ __('public-registration.hero.secondary_cta') }}</a>
                        </div>
                    @endif

                    <ul class="lfc-highlights">
                        <li>{{ __('public-registration.hero.highlights.review') }}</li>
                        <li>{{ __('public-registration.hero.highlights.bilingual') }}</li>
                        <li>{{ __('public-registration.hero.highlights.demo') }}</li>
                    </ul>
                </section>

                <section class="lfc-form-card" id="registration-form">
                    @if (! $season)
                        <div class="lfc-alert lfc-alert-info">
                            <strong>{{ __('public-registration.invite_only.title') }}</strong>
                            <p>{{ __('public-registration.invite_only.body') }}</p>
                        </div>
                    @elseif (! $registrationOpen)
                        <div class="lfc-alert lfc-alert-info">
                            <strong>{{ __('public-registration.closed.title') }}</strong>
                            <p>{{ __('public-registration.closed.body') }}</p>
                        </div>
                    @endif

                    @if (session('registration_submitted'))
                        <div class="lfc-alert lfc-alert-success">
                            <strong>{{ __('public-registration.success.title') }}</strong>
                            <p>{{ __('public-registration.success.body', ['name' => session('candidate_name')]) }}</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="lfc-alert lfc-alert-error">
                            <strong>{{ __('public-registration.nav.home') }}</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    @endif

                    @if ($season && $registrationOpen)
                    <form method="POST" action="{{ route('public.register.store', ['seasonSlug' => $seasonSlug, 'registrationSlug' => $registrationSlug, 'lang' => $locale]) }}" class="lfc-form">
                        @csrf

                        <div class="lfc-form-section">
                            <div class="lfc-section-heading">
                                <h3>{{ __('public-registration.sections.candidate') }}</h3>
                                <p>{{ __('public-registration.form.season_hint', ['season' => $season->name]) }}</p>
                            </div>

                            <div class="lfc-form-grid">
                                <label class="lfc-field">
                                    <span>{{ __('public-registration.form.full_name') }}</span>
                                    <input type="text" name="full_name" value="{{ old('full_name') }}" required>
                                </label>

                                <label class="lfc-field">
                                    <span>{{ __('public-registration.form.playing_position') }}</span>
                                    <select name="playing_position" required>
                                        <option value="" disabled @selected(old('playing_position') === null)></option>
                                        @foreach ($positionOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('playing_position') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="lfc-field">
                                    <span>{{ __('public-registration.form.year_of_birth') }}</span>
                                    <input type="number" name="year_of_birth" value="{{ old('year_of_birth') }}" min="1990" max="{{ now()->format('Y') }}" required>
                                </label>

                                <label class="lfc-field">
                                    <span>{{ __('public-registration.form.date_of_birth') }}</span>
                                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                                </label>

                                <label class="lfc-field">
                                    <span>{{ __('public-registration.form.country_of_birth') }}</span>
                                    <input type="text" name="country_of_birth" value="{{ old('country_of_birth') }}" required>
                                </label>

                                <label class="lfc-field">
                                    <span>{{ __('public-registration.form.citizenship') }}</span>
                                    <input type="text" name="citizenship" value="{{ old('citizenship') }}" required>
                                </label>

                                <label class="lfc-field">
                                    <span>{{ __('public-registration.form.year_arrived_qatar') }}</span>
                                    <input type="number" name="year_arrived_qatar" value="{{ old('year_arrived_qatar') }}" min="1990" max="{{ now()->format('Y') }}" required>
                                </label>

                                <label class="lfc-field">
                                    <span>{{ __('public-registration.form.school') }}</span>
                                    <input type="text" name="school" value="{{ old('school') }}" required>
                                </label>

                                <label class="lfc-field lfc-field-full">
                                    <span>{{ __('public-registration.form.previous_club') }}</span>
                                    <input type="text" name="previous_club" value="{{ old('previous_club') }}" required>
                                </label>
                            </div>
                        </div>

                        <div class="lfc-form-section">
                            <div class="lfc-section-heading">
                                <h3>{{ __('public-registration.sections.parent') }}</h3>
                                <p>{{ __('public-registration.form.email_hint') }}</p>
                            </div>

                            <div class="lfc-form-grid">
                                <label class="lfc-field">
                                    <span>{{ __('public-registration.form.parent_name') }}</span>
                                    <input type="text" name="parent_name" value="{{ old('parent_name') }}" required>
                                </label>

                                <label class="lfc-field">
                                    <span>{{ __('public-registration.form.parent_phone') }}</span>
                                    <input type="text" name="parent_phone" value="{{ old('parent_phone') }}" inputmode="tel" required>
                                </label>

                                <label class="lfc-field">
                                    <span>{{ __('public-registration.form.parent_whatsapp') }}</span>
                                    <input type="text" name="parent_whatsapp" value="{{ old('parent_whatsapp') }}" inputmode="tel" required>
                                </label>

                                <label class="lfc-field">
                                    <span>{{ __('public-registration.form.email') }}</span>
                                    <input type="email" name="email" value="{{ old('email') }}">
                                </label>
                            </div>
                        </div>

                        <div class="lfc-form-section">
                            <div class="lfc-section-heading">
                                <h3>{{ __('public-registration.sections.consent') }}</h3>
                                <p>{{ __('public-registration.form.duplicate_hint') }}</p>
                            </div>

                            <label class="lfc-checkbox">
                                <input type="checkbox" name="consent_given" value="1" @checked(old('consent_given')) required>
                                <span>{{ __('public-registration.form.consent') }}</span>
                            </label>
                        </div>

                        <button type="submit" class="lfc-button lfc-button-primary lfc-submit">
                            {{ __('public-registration.form.submit') }}
                        </button>
                    </form>
                    @endif
                </section>
            </main>
        </div>
    </body>
</html>
