<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Evidences\EvidenceTypeEnum;
use App\Models\Trip;
use App\Models\TripEvidence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TripEvidence>
 */
class TripEvidenceFactory extends Factory
{
    protected $model = TripEvidence::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'type' => EvidenceTypeEnum::KILOMETRAJE_SALIDA,
            'file_path' => 'evidences/odometers/'.fake()->uuid().'.jpg',
            'recorded_mileage' => fake()->numberBetween(1000, 100000),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function departureMileage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EvidenceTypeEnum::KILOMETRAJE_SALIDA,
        ]);
    }

    public function arrivalMileage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EvidenceTypeEnum::KILOMETRAJE_LLEGADA,
        ]);
    }

    public function fuelVoucher(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EvidenceTypeEnum::VOUCHER_COMBUSTIBLE,
        ]);
    }
}
