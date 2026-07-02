<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Validation\Validator;

class LatinText implements ValidationRule, ValidatorAwareRule
{
    protected Validator $validator;

    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $value)) {
            // Name the field in the message so the error is clear on its own
            // (e.g. in the summary alert), not just when read beside the input.
            $name = isset($this->validator)
                ? $this->validator->getDisplayableAttribute($attribute)
                : $attribute;

            $fail(__('validation.latin_only', ['attribute' => $name]));
        }
    }
}
