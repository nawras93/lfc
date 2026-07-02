<?php

namespace App\Support;

class Countries
{
    /**
     * @return array<string, string>
     */
    public static function countries(): array
    {
        return collect(config('countries'))
            ->mapWithKeys(fn (string $nationality, string $country): array => [$country => $country])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function nationalities(): array
    {
        return collect(config('countries'))
            ->mapWithKeys(fn (string $nationality): array => [$nationality => $nationality])
            ->all();
    }
}
