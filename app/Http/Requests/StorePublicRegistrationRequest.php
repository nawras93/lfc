<?php

namespace App\Http\Requests;

use App\Enums\PlayingPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'playing_position' => ['required', Rule::enum(PlayingPosition::class)],
            'year_of_birth' => ['required', 'integer', 'min:1990', "max:{$currentYear}"],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'country_of_birth' => ['required', 'string', 'max:255'],
            'citizenship' => ['required', 'string', 'max:255'],
            'year_arrived_qatar' => ['required', 'integer', 'min:1990', "max:{$currentYear}"],
            'school' => ['required', 'string', 'max:255'],
            'previous_club' => ['required', 'string', 'max:255'],
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_phone' => ['required', 'string', 'max:25', 'regex:/^[0-9+()\\-\\s]{7,25}$/'],
            'parent_whatsapp' => ['required', 'string', 'max:25', 'regex:/^[0-9+()\\-\\s]{7,25}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'consent_given' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => __('public-registration.form.full_name'),
            'playing_position' => __('public-registration.form.playing_position'),
            'year_of_birth' => __('public-registration.form.year_of_birth'),
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $yearOfBirth = (int) $this->input('year_of_birth');
            $dateOfBirth = $this->date('date_of_birth');

            if ($dateOfBirth !== null && $yearOfBirth !== (int) $dateOfBirth->format('Y')) {
                $validator->errors()->add('year_of_birth', __('public-registration.validation.birth_year_mismatch'));
            }
        });
    }
}
