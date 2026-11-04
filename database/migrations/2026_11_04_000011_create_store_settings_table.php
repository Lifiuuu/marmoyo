<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->default('Kedai Mie Marmoyo');
            $table->string('store_tagline')->default('Authentic Spicy Noodles & Dimsum');
            $table->string('store_address')->default('Jl. Marmoyo No. 12, Surabaya, Jawa Timur');
            $table->string('store_phone')->default('0812-3456-7890');
            $table->text('receipt_footer')->nullable();
            $table->decimal('packaging_fee', 10, 2)->default(2000.00);
            $table->decimal('tax_pb1_percent', 5, 2)->default(10.00);
            $table->boolean('promo_active')->default(true);
            $table->decimal('promo_discount', 10, 2)->default(3000.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};

