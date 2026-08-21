<?php

declare(strict_types=1);

namespace Tests\Unit\Requesters;

use App\Exceptions\Requesters\InvalidRequesterException;
use App\Models\Requester;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequesterInServiceInvariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_delete_requester_with_active_or_scheduled_trips(): void
    {
        $requester = Requester::factory()->create(['name' => 'Empresa Minera SAS']);
        Trip::factory()->scheduled()->create(['requester_id' => $requester->id]);

        $this->expectException(InvalidRequesterException::class);
        $this->expectExceptionMessage('No se puede eliminar el solicitante Empresa Minera SAS porque tiene servicios programados o en curso.');

        $requester->delete();
    }

    public function test_cannot_deactivate_requester_with_in_progress_trips(): void
    {
        $requester = Requester::factory()->create(['name' => 'Empresa Petrolera SAS', 'is_active' => true]);
        Trip::factory()->inProgress()->create(['requester_id' => $requester->id]);

        $this->expectException(InvalidRequesterException::class);
        $this->expectExceptionMessage('No se puede desactivar el solicitante Empresa Petrolera SAS porque tiene servicios programados o en curso.');

        $requester->update(['is_active' => false]);
    }

    public function test_can_delete_and_deactivate_requester_without_active_trips(): void
    {
        $requester = Requester::factory()->create(['name' => 'Empresa Libre SAS', 'is_active' => true]);

        $requester->update(['is_active' => false]);
        $this->assertFalse($requester->fresh()->is_active);

        $requester->delete();
        $this->assertSoftDeleted($requester);
    }
}
