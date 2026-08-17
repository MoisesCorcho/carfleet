<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->foreignId('requester_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->string('origin', 128);
            $table->string('destination', 128);
            $table->dateTime('scheduled_departure_at');
            $table->dateTime('scheduled_arrival_at')->nullable();
            $table->dateTime('actual_departure_at')->nullable();
            $table->dateTime('actual_arrival_at')->nullable();
            $table->unsignedInteger('initial_mileage')->nullable();
            $table->unsignedInteger('final_mileage')->nullable();
            $table->unsignedInteger('distance_traveled')->nullable();
            $table->string('status', 32)->default('programado');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
