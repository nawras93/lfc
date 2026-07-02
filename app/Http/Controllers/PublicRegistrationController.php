<?php

namespace App\Http\Controllers;

use App\Enums\Country;
use App\Enums\Nationality;
use App\Http\Requests\StorePublicRegistrationRequest;
use App\Models\Season;
use App\Services\PublicRegistrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PublicRegistrationController extends Controller
{
    public function __construct(
        private readonly PublicRegistrationService $publicRegistrationService,
    ) {}

    public function landing(): View
    {
        // Surface the registration form on the home page when the active season
        // has a live registration window; otherwise keep the invite-only notice.
        $season = Season::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();

        $registrationOpen = $season?->registrationIsOpen() ?? false;

        if (! $registrationOpen) {
            $season = null;
        }

        return view('public.register', [
            'season' => $season,
            'registrationOpen' => $registrationOpen,
            'registrationSlug' => $season?->registration_slug,
            'seasonSlug' => $season?->registrationSeasonSlug(),
            'isRtl' => app()->getLocale() === 'ar',
            'locale' => app()->getLocale(),
            'positionOptions' => $this->positionOptions(),
            'countryOptions' => Country::options(),
            'nationalityOptions' => Nationality::options(),
        ]);
    }

    public function create(string $seasonSlug, string $registrationSlug): View|RedirectResponse
    {
        $season = $this->publicRegistrationService->resolveSeasonFromRegistrationLink($registrationSlug);

        // The season-name slug is cosmetic. If it's stale (e.g. the season was
        // renamed after the link was shared), redirect once to the canonical URL.
        $canonicalSeasonSlug = $season->registrationSeasonSlug();

        if ($seasonSlug !== $canonicalSeasonSlug) {
            return redirect()->route('public.register.show', [
                'seasonSlug' => $canonicalSeasonSlug,
                'registrationSlug' => $registrationSlug,
                'lang' => app()->getLocale(),
            ], 301);
        }

        return view('public.register', [
            'season' => $season,
            'registrationOpen' => $season->registrationIsOpen(),
            'registrationSlug' => $registrationSlug,
            'seasonSlug' => $canonicalSeasonSlug,
            'isRtl' => app()->getLocale() === 'ar',
            'locale' => app()->getLocale(),
            'positionOptions' => $this->positionOptions(),
            'countryOptions' => Country::options(),
            'nationalityOptions' => Nationality::options(),
        ]);
    }

    public function store(StorePublicRegistrationRequest $request, string $seasonSlug, string $registrationSlug): RedirectResponse
    {
        $season = $this->publicRegistrationService->resolveSeasonFromRegistrationLink($registrationSlug);

        // Always redirect back to the canonical URL regardless of the slug posted.
        $seasonSlug = $season->registrationSeasonSlug();

        if (! $season->registrationIsOpen()) {
            return redirect()
                ->route('public.register.show', compact('seasonSlug', 'registrationSlug') + ['lang' => app()->getLocale()])
                ->withErrors([
                    'registration' => __('public-registration.closed.body'),
                ]);
        }

        $candidate = $this->publicRegistrationService->create($season, $request->validated());

        return redirect()
            ->route('public.register.show', compact('seasonSlug', 'registrationSlug') + ['lang' => app()->getLocale()])
            ->with('registration_submitted', true)
            ->with('candidate_name', $candidate->full_name);
    }

    /**
     * @return array<string, string>
     */
    private function positionOptions(): array
    {
        return [
            'goalkeeper' => __('public-registration.positions.goalkeeper'),
            'defender' => __('public-registration.positions.defender'),
            'midfielder' => __('public-registration.positions.midfielder'),
            'attacker' => __('public-registration.positions.attacker'),
        ];
    }
}
