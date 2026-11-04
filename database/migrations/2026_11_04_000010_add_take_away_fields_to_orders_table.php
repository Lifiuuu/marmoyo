<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type')->default('dine_in')->after('order_number');
                $table->index('order_type');
            }
            if (!Schema::hasColumn('orders', 'packaging_fee')) {
                $table->decimal('packaging_fee', 12, 2)->default(0)->after('service_fee');
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE orders ALTER COLUMN table_id DROP NOT NULL;');
        } elseif (DB::getDriverName() !== 'sqlite') {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('table_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'order_type')) {
                $table->dropIndex(['order_type']);
                $table->dropColumn('order_type');
            }
            if (Schema::hasColumn('orders', 'packaging_fee')) {
                $table->dropColumn('packaging_fee');
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE orders ALTER COLUMN table_id SET NOT NULL;');
        }
    }
};

