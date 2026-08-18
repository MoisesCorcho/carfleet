<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requesters', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 16);
            $table->string('document_number', 32);
            $table->string('name', 128);
            $table->string('company_name', 128)->nullable();
            $table->string('phone', 32);
            $table->string('email', 128)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['document_type', 'document_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requesters');
    }
};
