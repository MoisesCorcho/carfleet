<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Invoices\InvoiceStatusEnum;
use App\Models\Invoice;
use App\Models\Requester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'invoice_number' => 'FACT-'.date('Y').'-'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'requester_id' => Requester::factory(),
            'issue_date' => now()->toDateString(),
            'total_amount' => $this->faker->numberBetween(100000, 2000000),
            'status' => InvoiceStatusEnum::EMITIDA,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
