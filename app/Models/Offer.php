<?php

namespace App\Models;

use App\Enums\OfferAudience;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title',
    'body',
    'audience',
    'is_published',
    'valid_from',
    'valid_until',
])]
class Offer extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'audience' => OfferAudience::class,
            'is_published' => 'boolean',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }

    public function scopeVisibleTo($query, ParentAccount $parent, ?Carbon $at = null)
    {
        $at ??= now();

        $query
            ->where('is_published', true)
            ->where(function ($q) use ($at) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $at);
            })
            ->where(function ($q) use ($at) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $at);
            });

        if (! $parent->is_vvip) {
            $query->where('audience', OfferAudience::All);
        }

        return $query;
    }
}
