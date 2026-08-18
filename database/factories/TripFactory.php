<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Trips\TripStatusEnum;
use App\Models\Driver;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    protected $model = Trip::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = (int) date('Y');
        $number = fake()->unique()->numberBetween(1, 9999);
        $code = sprintf('TRIP-%d-%04d', $year, $number);

        return [
            'code' => $code,
            'requester_id' => Requester::factory(),
            'vehicle_id' => null,
            'driver_id' => null,
            'origin' => fake()->city().' - Centro',
            'destination' => fake()->city().' - Sede Operativa',
            'scheduled_departure_at' => now()->addDay()->setHour(8)->setMinute(0),
            'scheduled_arrival_at' => now()->addDay()->setHour(14)->setMinute(0),
            'actual_departure_at' => null,
            'actual_arrival_at' => null,
            'initial_mileage' => null,
            'final_mileage' => null,
            'distance_traveled' => null,
            'status' => TripStatusEnum::PROGRAMADO,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TripStatusEnum::PROGRAMADO,
            'vehicle_id' => null,
            'driver_id' => null,
        ]);
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TripStatusEnum::ASIGNADO,
            'vehicle_id' => Vehicle::factory()->assigned(),
            'driver_id' => Driver::factory()->active(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TripStatusEnum::EN_CURSO,
            'vehicle_id' => Vehicle::factory()->inTrip(),
            'driver_id' => Driver::factory()->active(),
            'actual_departure_at' => now()->subHour(),
            'initial_mileage' => 10000,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TripStatusEnum::FINALIZADO,
            'vehicle_id' => Vehicle::factory()->available(),
            'driver_id' => Driver::factory()->active(),
            'actual_departure_at' => now()->subHours(4),
            'actual_arrival_at' => now(),
            'initial_mileage' => 10000,
            'final_mileage' => 10250,
            'distance_traveled' => 250,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TripStatusEnum::CERRADO,
            'vehicle_id' => Vehicle::factory()->available(),
            'driver_id' => Driver::factory()->active(),
            'actual_departure_at' => now()->subDays(2),
            'actual_arrival_at' => now()->subDays(2)->addHours(4),
            'initial_mileage' => 10000,
            'final_mileage' => 10250,
            'distance_traveled' => 250,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TripStatusEnum::CANCELADO,
        ]);
    }
}
