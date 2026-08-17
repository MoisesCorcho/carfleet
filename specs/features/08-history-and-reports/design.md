# Design — F08: Historial y Reporte de Rendimiento

## 1. Domain Services & Queries

### Service: `App\Services\Reports\FleetPerformanceCalculatorService`

```php
declare(strict_types=1);

namespace App\Services\Reports;

use App\Models\Vehicle;

class FleetPerformanceCalculatorService
{
    public function calculateKmPerGallon(Vehicle $vehicle, ?string $startDate = null, ?string $endDate = null): float
    {
        $totalKm = $vehicle->trips()
            ->whereIn('status', ['finalizado', 'cerrado'])
            ->when($startDate, fn($q) => $q->whereDate('actual_departure_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('actual_arrival_at', '<=', $endDate))
            ->sum('distance_traveled');

        $totalGallons = $vehicle->fuelLogs()
            ->when($startDate, fn($q) => $q->whereDate('refuel_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('refuel_date', '<=', $endDate))
            ->sum('gallons');

        if ($totalGallons <= 0) {
            return 0.0;
        }

        return round($totalKm / $totalGallons, 2);
    }
}
```

---

## 2. UI Layer (Filament v4)

* **Widgets**: Filament Widgets (`StatsOverviewWidget` for total fleet mileage, total fuel cost, and average km/gallon).
* **Trip History Table**: Filterable table in `VehicleResource` view page.
