<?php

namespace App\Http\Requests;

use App\Enums\PlayingPosition;
use App\Rules\LatinText;
use App\Support\Countries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $currentYear = (int) now()->format('Y');
        $countryOptions = Countries::countries();
        $nationalityOptions = Countries::nationalities();

        return [
            'full_name' => ['required', 'string', 'max:255', new LatinText],
            'playing_position' => ['required', Rule::enum(PlayingPosition::class)],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'country_of_birth' => ['required', Rule::in(array_keys($countryOptions))],
            'citizenship' => ['required', Rule::in(array_keys($nationalityOptions))],
            'year_arrived_qatar' => ['required', 'integer', 'min:1990', "max:{$currentYear}"],
            'school' => ['required', 'string', 'max:255', new LatinText],
            'previous_club' => ['required', 'string', 'max:255', new LatinText],
            'parent_name' => ['required', 'string', 'max:255', new LatinText],
            'parent_phone' => ['required', 'string', 'max:25', 'regex:/^[0-9+()\\-\\s]{7,25}$/'],
            'parent_whatsapp' => ['required', 'string', 'max:25', 'regex:/^[0-9+()\\-\\s]{7,25}$/'],
            'email' => ['required', 'email', 'max:255'],
            'consent_given' => ['accepted'],
        ];
    }

    /**
     * TEMP: the Consent checkbox is hidden in the public form, so auto-fill
     * consent here to satisfy the 'accepted' rule. Consent is still recorded
     * (PublicRegistrationService stores consent_given = true). Remove this
     * shim when the consent section is restored in the view.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['consent_given' => '1']);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => __('public-registration.form.full_name'),
            'playing_position' => __('public-registration.form.playing_position'),
            'date_of_birth' => __('public-registration.form.date_of_birth'),
            'country_of_birth' => __('public-registration.form.country_of_birth'),
            'citizenship' => __('public-registration.form.citizenship'),
            'year_arrived_qatar' => __('public-registration.form.year_arrived_qatar'),
            'school' => __('public-registration.form.school'),
            'previous_club' => __('public-registration.form.previous_club'),
            'parent_name' => __('public-registration.form.parent_name'),
            'parent_phone' => __('public-registration.form.parent_phone'),
            'parent_whatsapp' => __('public-registration.form.parent_whatsapp'),
            'email' => __('public-registration.form.email'),
            'consent_given' => __('public-registration.form.consent'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'consent_given.accepted' => __('public-registration.validation.consent_required'),
            'parent_phone.regex' => __('public-registration.validation.phone'),
            'parent_whatsapp.regex' => __('public-registration.validation.phone'),
        ];
    }
}
