<?php

namespace App\Http\Middleware;

use App\Services\Table\TableSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureTableSession
{
    public function __construct(
        protected TableSessionService $tableService
    ) {}

    /**
     * Handle an incoming request.
     * Ensure that the customer has scanned a valid table QR code before accessing ordering features.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $table = $this->tableService->getCurrentTable();

        if (!$table) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Silakan scan QR Code yang tertera di meja makan Anda terlebih dahulu.',
                ], 403);
            }

            return response()->view('customer.invalid-table', [
                'message' => 'Silakan scan QR Code yang tertera di meja makan Anda terlebih dahulu untuk melihat menu dan melakukan pemesanan.',
            ], 403);
        }

        // If a table token is tracked in the session, ensure it still matches the table's current token
        if (Session::has('table_token') && Session::get('table_token') !== $table->qr_token) {
            $this->tableService->clearSession();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi QR Code meja Anda sudah kedaluwarsa. Silakan scan ulang stiker QR Code di meja Anda.',
                ], 403);
            }

            return response()->view('customer.invalid-table', [
                'message' => 'Sesi QR Code meja Anda sudah kedaluwarsa. Silakan scan ulang stiker QR Code di meja Anda.',
            ], 403);
        }

        return $next($request);
    }
}

