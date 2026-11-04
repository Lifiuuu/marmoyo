<?php

namespace App\Services\Table;

use App\Models\RestaurantTable;
use Illuminate\Support\Facades\Session;

class TableSessionService
{
    /**
     * Validate table and token, then bind to current customer session.
     */
    public function verifyAndSetSession(string $tableNumber, string $token): ?RestaurantTable
    {
        $table = RestaurantTable::where('table_number', $tableNumber)
            ->where('qr_token', $token)
            ->first();

        if ($table) {
            Session::put('table_id', $table->id);
            Session::put('table_number', $table->table_number);
            Session::put('table_token', $table->qr_token);
            return $table;
        }

        return null;
    }

    /**
     * Retrieve the currently bound table from active session.
     */
    public function getCurrentTable(): ?RestaurantTable
    {
        $tableId = Session::get('table_id');
        if (!$tableId) {
            return null;
        }

        return RestaurantTable::find($tableId);
    }

    /**
     * Generate secure QR Code access URL for a table.
     */
    public function generateTableQrUrl(RestaurantTable $table): string
    {
        return url("/scan?table={$table->table_number}&token={$table->qr_token}");
    }

    /**
     * Clear customer table session.
     */
    public function clearSession(): void
    {
        Session::forget(['table_id', 'table_number', 'table_token', 'cart']);
    }
}

