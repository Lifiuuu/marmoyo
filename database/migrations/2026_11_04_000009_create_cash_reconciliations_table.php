<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->date('shift_date');
            $table->decimal('starting_cash', 12, 2)->default(0);
            $table->decimal('total_cash_sales', 12, 2)->default(0);
            $table->decimal('total_qris_sales', 12, 2)->default(0);
            $table->decimal('actual_physical_cash', 12, 2)->default(0);
            $table->decimal('cash_difference', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('open'); // open, closed
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_reconciliations');
    }
};

