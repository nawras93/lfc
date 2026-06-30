<?php

namespace App\Models;

use App\Enums\RedemptionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'parent_account_id',
    'candidate_id',
    'redemption_item_id',
    'points_spent',
    'voucher_code',
    'status',
    'fulfilled_at',
    'fulfilled_by',
])]
class Redemption extends Model
{
    protected function casts(): array
    {
        return [
            'points_spent' => 'integer',
            'status' => RedemptionStatus::class,
            'fulfilled_at' => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentAccount::class, 'parent_account_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(RedemptionItem::class, 'redemption_item_id');
    }

    public function fulfilledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(PointTransaction::class, 'source');
    }
}
