<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Trips\TripStatusEnum;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\Vehicle;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use UnitEnum;

class SystemGuidePage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|UnitEnum|null $navigationGroup = 'Ayuda y Documentación';

    protected static ?int $navigationSort = 99;

    protected static ?string $slug = 'system-guide';

    protected static ?string $navigationLabel = 'Guía y Onboarding';

    protected static ?string $title = 'Guía Operacional y Arquitectura del Sistema';

    protected string $view = 'filament.pages.system-guide';

    public string $activeTab = 'workflow';

    public function getGuideContent(string $fileKey): string
    {
        $filePath = resource_path("docs/guide/{$fileKey}.md");
        if (! file_exists($filePath)) {
            return '<p class="text-gray-500">Documento no encontrado.</p>';
        }

        $markdown = file_get_contents($filePath);

        return (string) Str::markdown($markdown ?: '');
    }

    /**
     * @return array<string, int>
     */
    public function getSystemStats(): array
    {
        return [
            'vehicles_count' => Vehicle::count(),
            'drivers_count' => Driver::count(),
            'requesters_count' => Requester::count(),
            'trips_count' => Trip::count(),
            'closed_trips_count' => Trip::where('status', TripStatusEnum::CERRADO)->count(),
            'invoices_count' => Invoice::count(),
        ];
    }
}
