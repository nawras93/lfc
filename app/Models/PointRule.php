<?php

namespace App\Models;

use App\Enums\PointRuleType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'type',
    'points',
    'percentage',
    'base_amount',
    'team_id',
    'season_id',
    'priority',
    'is_active',
    'starts_at',
    'ends_at',
])]
class PointRule extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => PointRuleType::class,
            'points' => 'integer',
            'percentage' => 'decimal:2',
            'base_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function scopeActiveOn(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForFixture(Builder $query, Fixture $fixture): Builder
    {
        return $query->where(function (Builder $q) use ($fixture) {
            $q->whereNull('team_id')
                ->orWhere('team_id', $fixture->team_id);
        })->where(function (Builder $q) use ($fixture) {
            $q->whereNull('season_id')
                ->orWhere('season_id', $fixture->season_id);
        });
    }

    public function pointsValue(): int
    {
        if ($this->type === PointRuleType::Fixed) {
            return (int) $this->points;
        }

        return (int) round($this->base_amount * $this->percentage / 100);
    }
}
