<?php

namespace App\Models\Concerns;

use App\Enums\AppKey;
use App\Support\AppContext;
use Illuminate\Database\Eloquent\Builder;

trait ScopedToApp
{
    public static function bootScopedToApp(): void
    {
        static::addGlobalScope('app', function (Builder $query): void {
            $app = app(AppContext::class)->current();

            if ($app === null) {
                return;
            }

            $query->where($query->qualifyColumn('app'), $app->value);
        });
    }

    public function scopeForApp(Builder $query, AppKey $app): Builder
    {
        return $query->where($query->qualifyColumn('app'), $app->value);
    }

    public static function withoutAppScope(): Builder
    {
        return static::query()->withoutGlobalScope('app');
    }
}
