<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number', 16)->unique();
            $table->string('brand', 64);
            $table->string('model', 64);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('current_mileage')->default(0);
            $table->string('status', 32)->default('disponible');
            $table->string('fuel_type', 32)->default('gasolina');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
