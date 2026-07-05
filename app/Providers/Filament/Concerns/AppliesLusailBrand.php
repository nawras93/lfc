<?php

namespace App\Providers\Filament\Concerns;

use Filament\Enums\ThemeMode;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

trait AppliesLusailBrand
{
    protected function applyLusailBrand(Panel $panel): Panel
    {
        return $panel
            ->brandName('Lusail SC')
            ->brandLogo(asset('images/lusail-logo.png'))
            ->darkModeBrandLogo(asset('images/lusail-logo-light.png'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('images/favicon.png'))
            ->font('Tajawal')
            ->sidebarCollapsibleOnDesktop()
            ->defaultThemeMode(ThemeMode::Dark)
            ->colors([
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
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => View::make('filament.admin.locale-switcher')->render(),
            );
    }

    /**
     * Build-free brand polish injected into <head>: the mobile app's display
     * font (Changa) for headings, gold accents, and the navy hero login
     * background — all via CSS so no Vite theme compile is required.
     */
    protected function brandStyles(): string
    {
        return <<<'HTML'
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=changa:600,700" rel="stylesheet">
<style>
    @font-face{font-family:'Tajawal';font-weight:400;src:url('/fonts/tajawal/Tajawal-Regular.ttf') format('truetype');ascent-override:80%;descent-override:22%;line-gap-override:0%;}
    @font-face{font-family:'Tajawal';font-weight:500;src:url('/fonts/tajawal/Tajawal-Medium.ttf') format('truetype');ascent-override:80%;descent-override:22%;line-gap-override:0%;}
    @font-face{font-family:'Tajawal';font-weight:600;src:url('/fonts/tajawal/Tajawal-Bold.ttf') format('truetype');ascent-override:80%;descent-override:22%;line-gap-override:0%;}
    @font-face{font-family:'Tajawal';font-weight:700;src:url('/fonts/tajawal/Tajawal-Bold.ttf') format('truetype');ascent-override:80%;descent-override:22%;line-gap-override:0%;}
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
    /* Flag-only language toggle beside the user menu — mirrors the public site
       and the mobile app: the flag shown is the language you'll switch *to*.
       Bare flag, no pill/badge chrome. */
    .fi-admin-locale-switch {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: opacity 160ms ease;
    }
    .fi-admin-locale-switch:hover {
        opacity: .8;
    }
    .fi-admin-locale-flag {
        display: block;
        width: 1.9rem;
        height: 1.3rem;
        border-radius: .25rem;
        object-fit: cover;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .35);
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
