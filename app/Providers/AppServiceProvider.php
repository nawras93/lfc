<?php

namespace App\Providers;

use App\Support\AppContext;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Day-month-year is the format used across the admin panel — set it once
     * as the global default so every table column, infolist entry, and date
     * picker renders dd-mm-yyyy without per-field overrides.
     */
    private const DATE_FORMAT = 'd-m-Y';

    private const DATE_TIME_FORMAT = 'd-m-Y H:i';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AppContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(60)->by(
                $request->user()?->getAuthIdentifier() ?: $request->ip(),
            );
        });

        $this->configureDateFormats();
    }

    /**
     * Make dd-mm-yyyy the default everywhere Filament renders or edits a date:
     * table columns + infolist entries (via Table/Schema) and the date/datetime
     * pickers (DatePicker extends DateTimePicker, so this covers both inputs).
     */
    private function configureDateFormats(): void
    {
        Table::configureUsing(fn (Table $table): Table => $table
            ->defaultDateDisplayFormat(self::DATE_FORMAT)
            ->defaultDateTimeDisplayFormat(self::DATE_TIME_FORMAT));

        Schema::configureUsing(fn (Schema $schema): Schema => $schema
            ->defaultDateDisplayFormat(self::DATE_FORMAT)
            ->defaultDateTimeDisplayFormat(self::DATE_TIME_FORMAT));

        DateTimePicker::configureUsing(fn (DateTimePicker $picker): DateTimePicker => $picker
            ->defaultDateDisplayFormat(self::DATE_FORMAT)
            ->defaultDateTimeDisplayFormat(self::DATE_TIME_FORMAT));
    }
}
