<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\DriverPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    DriverPanelProvider::class,
];
