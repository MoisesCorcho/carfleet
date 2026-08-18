<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plate_number' => strtoupper(fake()->unique()->bothify('???-###')),
            'brand' => fake()->randomElement(['Toyota', 'Chevrolet', 'Ford', 'Nissan', 'Renault', 'Hyundai']),
            'model' => fake()->randomElement(['Hilux', 'D-Max', 'Ranger', 'Frontier', 'Duster', 'Tucson']),
            'year' => fake()->numberBetween(2015, (int) date('Y')),
            'current_mileage' => fake()->numberBetween(0, 150000),
            'status' => VehicleStatusEnum::DISPONIBLE,
            'fuel_type' => fake()->randomElement(FuelTypeEnum::cases()),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatusEnum::DISPONIBLE,
        ]);
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatusEnum::ASIGNADO,
        ]);
    }

    public function inTrip(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatusEnum::EN_VIAJE,
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatusEnum::MANTENIMIENTO,
        ]);
    }

    public function decommissioned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatusEnum::FUERA_DE_SERVICIO,
        ]);
    }
}
