<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Requesters\RequesterDocumentTypeEnum;
use App\Models\Requester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Requester>
 */
class RequesterFactory extends Factory
{
    protected $model = Requester::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company_name' => fake()->boolean(70) ? fake()->company().' S.A.S.' : null,
            'document_type' => fake()->randomElement(RequesterDocumentTypeEnum::cases()),
            'document_number' => fake()->unique()->numerify('90########'),
            'phone' => fake()->numerify('+57 300 #######'),
            'email' => fake()->safeEmail(),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_name' => fake()->company().' S.A.S.',
            'document_type' => RequesterDocumentTypeEnum::NIT,
            'document_number' => fake()->unique()->numerify('90########-#'),
        ]);
    }

    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_name' => null,
            'document_type' => RequesterDocumentTypeEnum::CC,
            'document_number' => fake()->unique()->numerify('10########'),
        ]);
    }

    public function withDocumentType(RequesterDocumentTypeEnum $type): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => $type,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
