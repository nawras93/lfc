<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetAdminLocale;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\View;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // Lusail SC identity — mirrors the mobile app (navy #113F71 crest
            // colour + Lusail gold accent). See mobile/lib/src/theme/app_theme.dart.
            ->brandName('Lusail SC')
            ->brandLogo(asset('images/lusail-logo.png'))
            ->darkModeBrandLogo(asset('images/lusail-logo-light.png'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('images/favicon.png'))
            ->font('Tajawal')
            ->defaultThemeMode(ThemeMode::Dark)
            ->colors([
                // Exact navy ramp from the mobile app's LfcColors (muted crest
                // navy — steel-blue mid-tones, not a saturated auto-generated blue).
                'primary' => [
                    50 => '#EAF2FA',
                    100 => '#D4E3F0',
                    200 => '#B5CEE3',
                    300 => '#8FB2CF',
                    400 => '#5C8AB8',
                    500 => '#2E659B',
                    600 => '#1C5288',
                    700 => '#113F71',
                    800 => '#0B3059',
                    900 => '#082848',
                    950 => '#06223C',
                ],
                'gray' => Color::Slate,
                'gold' => Color::hex('#C8A24A'),
                'info' => Color::hex('#2E659B'),
                'success' => Color::hex('#2E7D5B'),
                'warning' => Color::hex('#C8A24A'),
                'danger' => Color::Red,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString($this->brandStyles()),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): string => View::make('filament.admin.locale-switcher')->render(),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
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

    /**
     * Build-free brand polish injected into <head>: the mobile app's display
     * font (Changa) for headings, gold accents, and the navy hero login
     * background — all via CSS so no Vite theme compile is required.
     */
    private function brandStyles(): string
    {
        return <<<'HTML'
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=changa:600,700" rel="stylesheet">
<style>
    /* Changa display font for headings, matching the mobile app. */
    .fi-header-heading,
    .fi-modal-heading,
    .fi-section-header-heading {
        font-family: 'Changa', 'Tajawal', ui-sans-serif, system-ui, sans-serif;
        letter-spacing: .2px;
    }
    /* Gold hairline under the topbar — the mobile 'gold on navy' accent. */
    .fi-topbar {
        border-bottom: 1px solid rgba(200, 162, 74, .30);
    }
    /* Active sidebar item gets a gold inset accent alongside the navy fill. */
    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        box-shadow: inset 3px 0 0 0 #C8A24A;
    }
    /* Keep the brand lockup crisp and correctly sized. */
    .fi-logo img {
        max-height: 2.25rem;
        width: auto;
    }
    /* Login / simple pages: navy hero gradient behind the auth card,
       echoing the mobile login screen. */
    .fi-simple-layout {
        background:
            radial-gradient(1200px 620px at 50% -12%, #0A2A4B 0%, rgba(10, 42, 75, 0) 60%),
            linear-gradient(160deg, #06223C 0%, #0B3059 55%, #113F71 100%);
        background-attachment: fixed;
    }
</style>
HTML;
    }
}
