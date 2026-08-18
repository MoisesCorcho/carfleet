<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Drivers\DocumentTypeEnum;
use App\Enums\Drivers\DriverStatusEnum;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    protected $model = Driver::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'document_type' => DocumentTypeEnum::CC,
            'document_number' => fake()->unique()->numerify('10########'),
            'phone' => fake()->numerify('+57 300 #######'),
            'license_number' => fake()->unique()->bothify('LIC-######'),
            'license_expires_at' => fake()->dateTimeBetween('+6 months', '+5 years')->format('Y-m-d'),
            'status' => DriverStatusEnum::ACTIVO,
        ];
    }

    public function withDocumentType(DocumentTypeEnum $type): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => $type,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DriverStatusEnum::ACTIVO,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DriverStatusEnum::INACTIVO,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DriverStatusEnum::SUSPENDIDO,
        ]);
    }

    public function expiredLicense(): static
    {
        return $this->state(fn (array $attributes) => [
            'license_expires_at' => now()->subDays(30)->toDateString(),
        ]);
    }

    public function validLicense(): static
    {
        return $this->state(fn (array $attributes) => [
            'license_expires_at' => now()->addYears(2)->toDateString(),
        ]);
    }

    public function withoutLicenseExpiration(): static
    {
        return $this->state(fn (array $attributes) => [
            'license_expires_at' => null,
        ]);
    }
}
