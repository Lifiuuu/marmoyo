<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('payment_code')->unique(); // e.g. TUNAI-0824 or QRIS-0824
            $table->string('method'); // qris, cash
            $table->decimal('amount', 12, 2);
            $table->decimal('cash_received', 12, 2)->default(0);
            $table->decimal('cash_change', 12, 2)->default(0);
            $table->text('qris_payload')->nullable();
            $table->string('status')->default('pending'); // pending, success, failed, expired
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('payment_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

