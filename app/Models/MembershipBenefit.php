<?php

namespace App\Models;

use App\Support\Concerns\HasLocalizedContent;
use Database\Factories\MembershipBenefitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'membership_tier_id',
    'title',
    'title_ar',
    'description',
    'description_ar',
    'icon',
    'sort_order',
])]
class MembershipBenefit extends Model
{
    /** @use HasFactory<MembershipBenefitFactory> */
    use HasFactory, HasLocalizedContent;

    public function tier(): BelongsTo
    {
        return $this->belongsTo(MembershipTier::class, 'membership_tier_id');
    }
}
