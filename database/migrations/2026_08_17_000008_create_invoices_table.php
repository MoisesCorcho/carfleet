<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 32)->unique();
            $table->foreignId('requester_id')->constrained()->cascadeOnDelete();
            $table->date('issue_date');
            $table->unsignedBigInteger('total_amount'); // stored in integer currency units
            $table->string('status', 32)->default('issued');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_trip', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('subtotal_amount');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_trip');
        Schema::dropIfExists('invoices');
    }
};
