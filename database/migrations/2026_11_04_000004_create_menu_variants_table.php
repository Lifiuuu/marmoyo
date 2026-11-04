<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->string('group_name'); // e.g. "Tingkat Kepedasan", "Jenis Mie"
            $table->string('name'); // e.g. "Level 1 (Pedas Ringan)", "Mie Keriting"
            $table->decimal('additional_price', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_variants');
    }
};

