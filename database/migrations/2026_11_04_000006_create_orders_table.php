<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g. MRM-0824, MRM-TA01
            $table->string('order_type')->default('dine_in'); // dine_in, take_away
            $table->foreignId('table_id')->nullable()->constrained('restaurant_tables')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0); // Resto tax (PB1 10%)
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('packaging_fee', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);
            $table->string('status')->default('menunggu'); // menunggu, selesai, cancelled
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, expired, refunded
            $table->string('payment_method')->nullable(); // qris, cash
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('kitchen_notes')->nullable();
            $table->timestamps();

            $table->index(['table_id', 'status']);
            $table->index('order_type');
            $table->index('order_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

