<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelLog>
 */
class FuelLogFactory extends Factory
{
    protected $model = FuelLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'trip_id' => null,
            'driver_id' => null,
            'refuel_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'mileage_at_refuel' => fake()->numberBetween(1000, 100000),
            'gallons' => fake()->randomFloat(2, 5, 25),
            'total_cost' => fake()->numberBetween(50000, 350000),
            'voucher_number' => 'V-'.fake()->numerify('######'),
            'voucher_photo_path' => 'evidences/vouchers/'.fake()->uuid().'.jpg',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function forTrip(Trip $trip): static
    {
        return $this->state(fn (array $attributes) => [
            'trip_id' => $trip->id,
            'vehicle_id' => $trip->vehicle_id ?? Vehicle::factory(),
            'driver_id' => $trip->driver_id ?? Driver::factory(),
        ]);
    }
}
