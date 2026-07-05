<?php

namespace App\Models;

use App\Enums\AppKey;
use App\Models\Concerns\ScopedToApp;
use App\Support\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'app',
    'title',
    'title_ar',
    'excerpt',
    'excerpt_ar',
    'body',
    'body_ar',
    'image_path',
    'is_published',
    'published_at',
])]
class NewsPost extends Model
{
    use HasFactory, HasLocalizedContent, ScopedToApp, SoftDeletes;

    protected function casts(): array
    {
        return [
            'app' => AppKey::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
