<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Filament\Widgets\TopRequestersChartWidget;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TopRequestersChartWidgetTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super_admin');
    }

    public function test_top_requesters_chart_widget_renders_top_clients_by_completed_trips(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        $clientA = Requester::factory()->create(['name' => 'Empresa Alfa']);
        $clientB = Requester::factory()->create(['name' => 'Empresa Beta']);

        // Client A: 3 completed trips
        Trip::factory()->closed()->create(['requester_id' => $clientA->id]);
        Trip::factory()->closed()->create(['requester_id' => $clientA->id]);
        Trip::factory()->completed()->create(['requester_id' => $clientA->id]);

        // Client B: 1 completed trip
        Trip::factory()->closed()->create(['requester_id' => $clientB->id]);

        Livewire::test(TopRequestersChartWidget::class)
            ->assertSuccessful();
    }

    public function test_top_requesters_chart_widget_handles_empty_database(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        Livewire::test(TopRequestersChartWidget::class)
            ->assertSuccessful();
    }
}
