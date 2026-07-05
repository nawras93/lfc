<?php

namespace App\Models;

use App\Enums\AppKey;
use App\Enums\OfferAudience;
use App\Models\Concerns\ScopedToApp;
use App\Support\Concerns\HasLocalizedContent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title',
    'title_ar',
    'body',
    'body_ar',
    'audience',
    'app',
    'is_published',
    'valid_from',
    'valid_until',
])]
class Offer extends Model
{
    use HasFactory, HasLocalizedContent, ScopedToApp, SoftDeletes;

    protected function casts(): array
    {
        return [
            'audience' => OfferAudience::class,
            'app' => AppKey::class,
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
