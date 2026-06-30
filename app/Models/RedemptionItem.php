<?php

namespace App\Models;

use App\Enums\RedemptionType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'description',
    'type',
    'points_cost',
    'stock',
    'is_active',
    'valid_from',
    'valid_until',
])]
class RedemptionItem extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => RedemptionType::class,
            'points_cost' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }

    public function scopeAvailable($query, ?Carbon $at = null)
    {
        $at ??= now();

        return $query
            ->where('is_active', true)
            ->where(function ($q) use ($at) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $at);
            })
            ->where(function ($q) use ($at) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $at);
            })
            ->where(function ($q) {
                $q->whereNull('stock')->orWhere('stock', '>', 0);
            });
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }
}
