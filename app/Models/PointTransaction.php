<?php

namespace App\Models;

use App\Enums\PointTransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'candidate_id',
    'type',
    'points',
    'point_rule_id',
    'source_type',
    'source_id',
    'reason',
    'created_by',
])]
class PointTransaction extends Model
{
    protected function casts(): array
    {
        return [
            'type' => PointTransactionType::class,
            'points' => 'integer',
        ];
    }

    public static function booted(): void
    {
        static::updating(function (): never {
            throw new \LogicException('Point transactions are append-only. Use a reverse or adjust entry instead.');
        });

        static::deleting(function (): never {
            throw new \LogicException('Point transactions are append-only. Use a reverse or adjust entry instead.');
        });
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function pointRule(): BelongsTo
    {
        return $this->belongsTo(PointRule::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
