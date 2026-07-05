<?php

namespace App\Models;

use App\Enums\AppKey;
use App\Models\Concerns\ScopedToApp;
use App\Support\Concerns\HasLocalizedContent;
use Database\Factories\MembershipTierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'app',
    'name',
    'name_ar',
    'level',
    'accent_color',
    'is_active',
])]
class MembershipTier extends Model
{
    /** @use HasFactory<MembershipTierFactory> */
    use HasFactory, HasLocalizedContent, ScopedToApp;

    protected function casts(): array
    {
        return [
            'app' => AppKey::class,
            'is_active' => 'boolean',
        ];
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(MembershipBenefit::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
