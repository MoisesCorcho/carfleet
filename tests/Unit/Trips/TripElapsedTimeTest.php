<?php

declare(strict_types=1);

namespace Tests\Unit\Trips;

use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TripElapsedTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_null_when_trip_has_not_started(): void
    {
        $trip = Trip::factory()->scheduled()->create([
            'actual_departure_at' => null,
        ]);

        $this->assertNull($trip->elapsed_time_for_humans);
        $this->assertNull($trip->elapsed_minutes);
    }

    public function test_it_calculates_elapsed_time_for_in_progress_trip(): void
    {
        Carbon::setTestNow('2026-08-21 14:00:00');

        $trip = Trip::factory()->inProgress()->create([
            'actual_departure_at' => '2026-08-21 11:30:00', // 2 hours 30 mins ago
            'actual_arrival_at' => null,
        ]);

        $this->assertSame(150, $trip->elapsed_minutes);
        $this->assertSame('2h 30m', $trip->elapsed_time_for_humans);

        Carbon::setTestNow();
    }

    public function test_it_formats_minutes_only_when_under_one_hour(): void
    {
        Carbon::setTestNow('2026-08-21 10:45:00');

        $trip = Trip::factory()->inProgress()->create([
            'actual_departure_at' => '2026-08-21 10:15:00', // 30 mins ago
            'actual_arrival_at' => null,
        ]);

        $this->assertSame(30, $trip->elapsed_minutes);
        $this->assertSame('30m', $trip->elapsed_time_for_humans);

        Carbon::setTestNow();
    }

    public function test_it_calculates_total_duration_for_completed_trip(): void
    {
        $trip = Trip::factory()->completed()->create([
            'actual_departure_at' => '2026-08-20 08:00:00',
            'actual_arrival_at' => '2026-08-20 13:45:00', // 5h 45m
        ]);

        $this->assertSame(345, $trip->elapsed_minutes);
        $this->assertSame('5h 45m', $trip->elapsed_time_for_humans);
    }
}
