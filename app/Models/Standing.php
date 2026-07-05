<?php

namespace App\Models;

use App\Enums\AppKey;
use App\Models\Concerns\ScopedToApp;
use App\Support\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'app',
    'club_name',
    'club_name_ar',
    'played',
    'won',
    'drawn',
    'lost',
    'goals_for',
    'goals_against',
    'points',
    'is_own_club',
])]
class Standing extends Model
{
    use HasFactory, HasLocalizedContent, ScopedToApp;

    protected function casts(): array
    {
        return [
            'app' => AppKey::class,
            'is_own_club' => 'boolean',
        ];
    }

    protected function goalDifference(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->goals_for - $this->goals_against,
        );
    }
}
