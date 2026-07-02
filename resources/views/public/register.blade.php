<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('public-registration.meta.title') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
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
                    <img class="lfc-hero-banner" src="{{ asset('images/registration-hero.png') }}" alt="{{ __('public-registration.hero.eyebrow') }}">

                    <div class="lfc-hero-body">
                        <p class="lfc-hero-kicker">{{ __('public-registration.hero.eyebrow') }}</p>
                        <h2>{{ __('public-registration.hero.title') }}</h2>

                        <p class="lfc-hero-greeting">{{ __('public-registration.hero.greeting') }}</p>
                        @foreach (__('public-registration.hero.body') as $paragraph)
                            <p class="lfc-hero-copy">{{ $paragraph }}</p>
                        @endforeach

                        <p class="lfc-hero-signoff">
                            {{ __('public-registration.hero.signoff') }}<br>
                            <strong>{{ __('public-registration.hero.signoff_org') }}</strong>
                        </p>

                        @if ($season && $registrationOpen && $registrationSlug)
                            <div class="lfc-hero-actions">
                                <a href="#registration-form" class="lfc-button lfc-button-primary">{{ __('public-registration.hero.primary_cta') }}</a>
                            </div>
                        @endif
                    </div>
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
                    @elseif (! $registrationSlug)
                        <div class="lfc-alert lfc-alert-info">
                            <strong>{{ __('public-registration.unavailable.title') }}</strong>
                            <p>{{ __('public-registration.unavailable.body') }}</p>
                        </div>
                    @endif

                    @if (session('registration_submitted'))
                        <div class="lfc-alert lfc-alert-success">
                            <strong>{{ __('public-registration.success.title') }}</strong>
                            <p>{{ __('public-registration.success.body', ['name' => session('candidate_name')]) }}</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="lfc-alert lfc-alert-error" role="alert">
                            <strong>{{ __('public-registration.errors.title') }}</strong>
                            <p>{{ __('public-registration.errors.intro') }}</p>
                            <ul class="lfc-alert-list">
                                @foreach ($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($season && $registrationOpen && $registrationSlug)
                    <form method="POST" action="{{ route('public.register.store', ['seasonSlug' => $seasonSlug, 'registrationSlug' => $registrationSlug, 'lang' => $locale]) }}" class="lfc-form">
                        @csrf

                        <div class="lfc-form-section">
                            <div class="lfc-section-heading">
                                <div class="lfc-section-heading-top">
                                    <h3>{{ __('public-registration.sections.candidate') }}</h3>
                                    <span class="lfc-section-note">{{ __('public-registration.form.latin_note') }}</span>
                                </div>
                                <p>{{ __('public-registration.form.season_hint', ['season' => $season->name]) }}</p>
                            </div>

                            <div class="lfc-form-grid">
                                <label class="lfc-field @error('full_name') lfc-field-error @enderror">
                                    <span class="lfc-field-label">{{ __('public-registration.form.full_name') }}</span>
                                    <input type="text" name="full_name" value="{{ old('full_name') }}" dir="ltr" lang="en" class="js-latin-input" data-latin-message="{{ __('validation.latin_only', ['attribute' => __('public-registration.form.full_name')]) }}" @error('full_name') aria-invalid="true" @enderror required>
                                    @error('full_name')<span class="lfc-field-message">{{ $message }}</span>@enderror
                                </label>

                                <label class="lfc-field @error('playing_position') lfc-field-error @enderror">
                                    <span class="lfc-field-label">{{ __('public-registration.form.playing_position') }}</span>
                                    <select name="playing_position" class="js-searchable-select" data-placeholder="{{ __('public-registration.form.select_placeholder') }}" data-no-results="{{ __('public-registration.form.no_results') }}" @error('playing_position') aria-invalid="true" @enderror required>
                                        <option value="" disabled @selected(old('playing_position') === null)></option>
                                        @foreach ($positionOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('playing_position') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('playing_position')<span class="lfc-field-message">{{ $message }}</span>@enderror
                                </label>

                                <label class="lfc-field @error('date_of_birth') lfc-field-error @enderror">
                                    <span class="lfc-field-label">{{ __('public-registration.form.date_of_birth') }}</span>
                                    <input type="text" name="date_of_birth" class="js-date-input" value="{{ old('date_of_birth') }}" placeholder="dd-mm-yyyy" @error('date_of_birth') aria-invalid="true" @enderror required>
                                    @error('date_of_birth')<span class="lfc-field-message">{{ $message }}</span>@enderror
                                </label>

                                <label class="lfc-field @error('country_of_birth') lfc-field-error @enderror">
                                    <span class="lfc-field-label">{{ __('public-registration.form.country_of_birth') }}</span>
                                    <select name="country_of_birth" class="js-searchable-select" data-placeholder="{{ __('public-registration.form.select_placeholder') }}" data-no-results="{{ __('public-registration.form.no_results') }}" @error('country_of_birth') aria-invalid="true" @enderror required>
                                        <option value="" disabled @selected(old('country_of_birth') === null)></option>
                                        @foreach ($countryOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('country_of_birth') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('country_of_birth')<span class="lfc-field-message">{{ $message }}</span>@enderror
                                </label>

                                <label class="lfc-field @error('citizenship') lfc-field-error @enderror">
                                    <span class="lfc-field-label">{{ __('public-registration.form.citizenship') }}</span>
                                    <select name="citizenship" class="js-searchable-select" data-placeholder="{{ __('public-registration.form.select_placeholder') }}" data-no-results="{{ __('public-registration.form.no_results') }}" @error('citizenship') aria-invalid="true" @enderror required>
                                        <option value="" disabled @selected(old('citizenship') === null)></option>
                                        @foreach ($nationalityOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('citizenship') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('citizenship')<span class="lfc-field-message">{{ $message }}</span>@enderror
                                </label>

                                <label class="lfc-field @error('year_arrived_qatar') lfc-field-error @enderror">
                                    <span class="lfc-field-label">{{ __('public-registration.form.year_arrived_qatar') }}</span>
                                    <input type="number" name="year_arrived_qatar" value="{{ old('year_arrived_qatar') }}" min="1990" max="{{ now()->format('Y') }}" @error('year_arrived_qatar') aria-invalid="true" @enderror required>
                                    @error('year_arrived_qatar')<span class="lfc-field-message">{{ $message }}</span>@enderror
                                </label>

                                <label class="lfc-field @error('school') lfc-field-error @enderror">
                                    <span class="lfc-field-label">{{ __('public-registration.form.school') }}</span>
                                    <input type="text" name="school" value="{{ old('school') }}" dir="ltr" lang="en" class="js-latin-input" data-latin-message="{{ __('validation.latin_only', ['attribute' => __('public-registration.form.school')]) }}" @error('school') aria-invalid="true" @enderror required>
                                    @error('school')<span class="lfc-field-message">{{ $message }}</span>@enderror
                                </label>

                                <label class="lfc-field @error('previous_club') lfc-field-error @enderror">
                                    <span class="lfc-field-label">{{ __('public-registration.form.previous_club') }}</span>
                                    <input type="text" name="previous_club" value="{{ old('previous_club') }}" dir="ltr" lang="en" class="js-latin-input" data-latin-message="{{ __('validation.latin_only', ['attribute' => __('public-registration.form.previous_club')]) }}" @error('previous_club') aria-invalid="true" @enderror required>
                                    @error('previous_club')<span class="lfc-field-message">{{ $message }}</span>@enderror
                                </label>
                            </div>
                        </div>

                        <div class="lfc-form-section">
                            <div class="lfc-section-heading">
                                <div class="lfc-section-heading-top">
                                    <h3>{{ __('public-registration.sections.parent') }}</h3>
                                    <span class="lfc-section-note">{{ __('public-registration.form.latin_note') }}</span>
                                </div>
                                <p>{{ __('public-registration.form.email_hint') }}</p>
                            </div>

                            <div class="lfc-form-grid">
                                <label class="lfc-field @error('parent_name') lfc-field-error @enderror">
                                    <span class="lfc-field-label">{{ __('public-registration.form.parent_name') }}</span>
                                    <input type="text" name="parent_name" value="{{ old('parent_name') }}" dir="ltr" lang="en" class="js-latin-input" data-latin-message="{{ __('validation.latin_only', ['attribute' => __('public-registration.form.parent_name')]) }}" @error('parent_name') aria-invalid="true" @enderror required>
                                    @error('parent_name')<span class="lfc-field-message">{{ $message }}</span>@enderror
                                </label>

                                <label class="lfc-field @error('parent_phone') lfc-field-error @enderror">
                                    <span class="lfc-field-label">{{ __('public-registration.form.parent_phone') }}</span>
                                    <input type="text" name="parent_phone" value="{{ old('parent_phone') }}" inputmode="tel" dir="ltr" @error('parent_phone') aria-invalid="true" @enderror required>
                                    @error('parent_phone')<span class="lfc-field-message">{{ $message }}</span>@enderror
                                </label>

                                <label class="lfc-field @error('parent_whatsapp') lfc-field-error @enderror">
                                    <span class="lfc-field-label">{{ __('public-registration.form.parent_whatsapp') }}</span>
                                    <input type="text" name="parent_whatsapp" value="{{ old('parent_whatsapp') }}" inputmode="tel" dir="ltr" @error('parent_whatsapp') aria-invalid="true" @enderror required>
                                    @error('parent_whatsapp')<span class="lfc-field-message">{{ $message }}</span>@enderror
                                </label>

                                <label class="lfc-field @error('email') lfc-field-error @enderror">
                                    <span class="lfc-field-label">{{ __('public-registration.form.email') }}</span>
                                    <input type="email" name="email" value="{{ old('email') }}" dir="ltr" @error('email') aria-invalid="true" @enderror required>
                                    @error('email')<span class="lfc-field-message">{{ $message }}</span>@enderror
                                </label>
                            </div>
                        </div>

                        {{-- TEMP: Consent section hidden by request. Consent is still
                             recorded server-side (PublicRegistrationService sets consent_given=true,
                             StorePublicRegistrationRequest auto-fills it). Restore this block and
                             remove those two temporary shims to bring the checkbox back. --}}
                        {{--
                        <div class="lfc-form-section">
                            <div class="lfc-section-heading">
                                <h3>{{ __('public-registration.sections.consent') }}</h3>
                                <p>{{ __('public-registration.form.duplicate_hint') }}</p>
                            </div>

                            <label class="lfc-checkbox @error('consent_given') lfc-field-error @enderror">
                                <input type="checkbox" name="consent_given" value="1" @checked(old('consent_given')) @error('consent_given') aria-invalid="true" @enderror required>
                                <span>{{ __('public-registration.form.consent') }}</span>
                            </label>
                            @error('consent_given')<span class="lfc-field-message">{{ $message }}</span>@enderror
                        </div>
                        --}}

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
