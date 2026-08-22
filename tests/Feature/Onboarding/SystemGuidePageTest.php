<?php

declare(strict_types=1);

namespace Tests\Feature\Onboarding;

use App\Filament\Pages\SystemGuidePage;
use App\Filament\Widgets\OnboardingQuickStartWidget;
use App\Models\Driver;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

describe('Onboarding & System Guide Feature Tests', function (): void {
    beforeEach(function (): void {
        $this->seed(RoleSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');
        $this->actingAs($this->admin);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    });

    test('admin can access system guide page and view operational documentation', function (): void {
        Livewire::test(SystemGuidePage::class)
            ->assertSuccessful()
            ->assertSee('Centro de Documentación y Guía del Sistema')
            ->assertSee('Flujo Extremo a Extremo')
            ->assertSee('Checklist de Puesta en Marcha Inicial');
    });

    test('onboarding quick start widget renders progress correctly on dashboard', function (): void {
        // Step 1: Empty database -> 0 steps (0%)
        Livewire::test(OnboardingQuickStartWidget::class)
            ->assertSuccessful()
            ->assertSee('0 de 4 pasos')
            ->assertSee('0%');

        // Step 2: Create a vehicle -> 1 step (25%)
        Vehicle::factory()->create();

        Livewire::test(OnboardingQuickStartWidget::class)
            ->assertSuccessful()
            ->assertSee('1 de 4 pasos')
            ->assertSee('25%');

        // Step 3: Create driver, requester, trip -> 4 steps (100%)
        Driver::factory()->create();
        Requester::factory()->create();
        Trip::factory()->create();

        Livewire::test(OnboardingQuickStartWidget::class)
            ->assertSuccessful()
            ->assertSee('4 de 4 pasos')
            ->assertSee('100%');
    });
});
