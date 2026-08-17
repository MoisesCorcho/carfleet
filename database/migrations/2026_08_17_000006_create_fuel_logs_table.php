<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('refuel_date');
            $table->unsignedInteger('mileage_at_refuel');
            $table->decimal('gallons', 8, 2);
            $table->unsignedBigInteger('total_cost'); // stored in integer currency units
            $table->string('voucher_number', 64)->nullable();
            $table->string('voucher_photo_path', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
    }
};
