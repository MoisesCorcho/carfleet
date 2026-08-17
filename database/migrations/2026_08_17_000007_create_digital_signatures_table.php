<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('signer_name', 128);
            $table->string('signature_path', 255);
            $table->dateTime('signed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};
