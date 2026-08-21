<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DigitalSignature;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalSignature>
 */
class DigitalSignatureFactory extends Factory
{
    protected $model = DigitalSignature::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'signer_name' => fake()->name(),
            'signature_path' => 'evidences/signatures/signature_'.fake()->uuid().'.png',
            'signed_at' => now(),
        ];
    }
}
