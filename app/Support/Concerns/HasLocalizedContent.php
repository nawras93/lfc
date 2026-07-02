<?php

namespace App\Support\Concerns;

trait HasLocalizedContent
{
    public function localized(string $field): ?string
    {
        if (app()->getLocale() === 'ar' && filled($this->getAttribute($field.'_ar'))) {
            return $this->getAttribute($field.'_ar');
        }

        return $this->getAttribute($field);
    }
}
