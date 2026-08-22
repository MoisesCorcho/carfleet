<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Driver;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\Vehicle;
use Filament\Widgets\Widget;

class OnboardingQuickStartWidget extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.onboarding-quick-start-widget';

    /**
     * @return array{
     *     vehicles: int,
     *     drivers: int,
     *     requesters: int,
     *     trips: int,
     *     completed_steps: int,
     *     progress_percentage: int
     * }
     */
    public function getOnboardingStatus(): array
    {
        $vehicles = Vehicle::count();
        $drivers = Driver::count();
        $requesters = Requester::count();
        $trips = Trip::count();

        $completedSteps = 0;
        if ($vehicles > 0) {
            $completedSteps++;
        }
        if ($drivers > 0) {
            $completedSteps++;
        }
        if ($requesters > 0) {
            $completedSteps++;
        }
        if ($trips > 0) {
            $completedSteps++;
        }

        $progressPercentage = (int) round(($completedSteps / 4) * 100);

        return [
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'requesters' => $requesters,
            'trips' => $trips,
            'completed_steps' => $completedSteps,
            'progress_percentage' => $progressPercentage,
        ];
    }
}
