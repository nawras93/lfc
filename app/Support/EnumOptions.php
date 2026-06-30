<?php

namespace App\Support;

use Filament\Support\Contracts\HasLabel;

class EnumOptions
{
    /**
     * @param  class-string<\BackedEnum&HasLabel>  $enumClass
     * @return array<string, string>
     */
    public static function for(string $enumClass): array
    {
        return collect($enumClass::cases())
            ->mapWithKeys(fn (HasLabel&\BackedEnum $case): array => [$case->value => (string) $case->getLabel()])
            ->all();
    }
}
