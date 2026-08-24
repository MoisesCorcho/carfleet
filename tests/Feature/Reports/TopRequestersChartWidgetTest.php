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
use Illuminate\Support\Carbon;
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

        $clientA = Requester::factory()->create(['name' => 'Roberto Sánchez', 'company_name' => 'Consorcio Vial']);
        $clientB = Requester::factory()->create(['name' => 'Patricia Ortiz', 'company_name' => null]);

        // Client A: 3 completed trips
        Trip::factory()->closed()->create(['requester_id' => $clientA->id]);
        Trip::factory()->closed()->create(['requester_id' => $clientA->id]);
        Trip::factory()->completed()->create(['requester_id' => $clientA->id]);

        // Client B: 1 completed trip
        Trip::factory()->closed()->create(['requester_id' => $clientB->id]);

        $test = Livewire::test(TopRequestersChartWidget::class, ['filter' => 'all'])
            ->assertSuccessful();

        $data = (new \ReflectionMethod($test->instance(), 'getData'))->invoke($test->instance());

        $this->assertSame(['Consorcio Vial — Roberto Sánchez', 'Patricia Ortiz'], $data['labels']);
        $this->assertSame([3, 1], $data['datasets'][0]['data']);
        $this->assertCount(2, $data['datasets'][0]['backgroundColor']);
        $this->assertSame('#3b82f6', $data['datasets'][0]['backgroundColor'][0]);
        $this->assertSame('#10b981', $data['datasets'][0]['backgroundColor'][1]);
    }

    public function test_top_requesters_chart_widget_filters_by_selected_time_period(): void
    {
        Carbon::setTestNow('2026-08-21 12:00:00');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        $clientAugust = Requester::factory()->create(['name' => 'Cliente Agosto']);
        $clientJuly = Requester::factory()->create(['name' => 'Cliente Julio']);

        // Trip in August
        Trip::factory()->closed()->create([
            'requester_id' => $clientAugust->id,
            'actual_departure_at' => '2026-08-10 08:00:00',
        ]);

        // Trip in July
        Trip::factory()->closed()->create([
            'requester_id' => $clientJuly->id,
            'actual_departure_at' => '2026-07-10 08:00:00',
        ]);

        $testMonth = Livewire::test(TopRequestersChartWidget::class, ['filter' => 'month'])
            ->assertSuccessful();

        $dataMonth = (new \ReflectionMethod($testMonth->instance(), 'getData'))->invoke($testMonth->instance());
        $this->assertSame([$clientAugust->display_name], $dataMonth['labels']);
        $this->assertSame([1], $dataMonth['datasets'][0]['data']);

        // Switch to 'all'
        $testAll = Livewire::test(TopRequestersChartWidget::class, ['filter' => 'all'])
            ->assertSuccessful();

        $dataAll = (new \ReflectionMethod($testAll->instance(), 'getData'))->invoke($testAll->instance());
        $this->assertCount(2, $dataAll['labels']);
        $this->assertContains($clientAugust->display_name, $dataAll['labels']);
        $this->assertContains($clientJuly->display_name, $dataAll['labels']);

        Carbon::setTestNow();
    }

    public function test_top_requesters_chart_widget_handles_empty_database(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->adminUser);

        Livewire::test(TopRequestersChartWidget::class)
            ->assertSuccessful();
    }
}
