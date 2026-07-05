<?php

namespace App\Providers\Filament;

use App\Enums\AppKey;
use App\Http\Middleware\SetAdminLocale;
use App\Http\Middleware\SetAppContext;
use App\Providers\Filament\Concerns\AppliesLusailBrand;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminAppTwoPanelProvider extends PanelProvider
{
    use AppliesLusailBrand;

    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->id('admin-app-two')
            ->path('admin-app-two')
            ->login();

        $panel = $this->applyLusailBrand($panel);

        return $panel
            // App two gets its own empty discovery tree until T19-T21 add resources.
            ->discoverResources(in: app_path('Filament/AppTwo/Resources'), for: 'App\Filament\AppTwo\Resources')
            ->discoverPages(in: app_path('Filament/AppTwo/Pages'), for: 'App\Filament\AppTwo\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/AppTwo/Widgets'), for: 'App\Filament\AppTwo\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetAppContext::class.':'.AppKey::AppTwo->value,
                SetAdminLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
