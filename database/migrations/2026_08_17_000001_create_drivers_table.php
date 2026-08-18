<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name', 128);
            $table->string('document_type', 16)->default('CC');
            $table->string('document_number', 32);
            $table->string('phone', 32);
            $table->string('license_number', 32)->unique();
            $table->date('license_expires_at')->nullable();
            $table->string('status', 32)->default('activo');
            $table->timestamps();

            $table->unique(['document_type', 'document_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
