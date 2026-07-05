<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminAppOnePanelProvider;
use App\Providers\Filament\AdminAppTwoPanelProvider;

return [
    AppServiceProvider::class,
    AdminAppOnePanelProvider::class,
    AdminAppTwoPanelProvider::class,
];
