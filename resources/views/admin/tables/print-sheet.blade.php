<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Cetak QR Code Meja - Kedai Mie Marmoyo</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #fff; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .card { border: 2px dashed #333; border-radius: 12px; padding: 15px; text-align: center; page-break-inside: avoid; }
        .header { font-size: 14px; font-weight: bold; color: #dc2626; margin-bottom: 4px; }
        .sub { font-size: 10px; color: #666; margin-bottom: 10px; }
        .table-badge { font-size: 18px; font-weight: 900; background: #000; color: #fff; display: inline-block; padding: 4px 12px; border-radius: 6px; margin-bottom: 10px; }
        .qr-img { width: 140px; height: 140px; margin: 0 auto; display: block; }
        .instructions { font-size: 9px; color: #555; margin-top: 10px; font-weight: bold; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; padding: 12px; background: #fef2f2; border: 1px solid #f87171; border-radius: 8px;">
        <button onclick="window.print()" style="background: #dc2626; color: white; padding: 8px 16px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak Lembar Stiker Meja Sekarang
        </button>
        <span style="font-size: 12px; margin-left: 12px; color: #991b1b;">
            Gunakan kertas stiker A4 untuk ditempelkan di sudut meja kedai.
        </span>
    </div>

    <div class="grid">
        @foreach($tables as $table)
            @php $qrUrl = url("/scan?table={$table->table_number}&token={$table->qr_token}"); @endphp
            <div class="card">
                <div class="header">KEDAI MIE MARMOYO</div>
                <div class="sub">Self-Ordering System</div>
                <div class="table-badge">MEJA {{ $table->table_number }}</div>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($qrUrl) }}" alt="QR Meja {{ $table->table_number }}" class="qr-img">
                <div class="instructions">
                    SCAN UNTUK PESAN & BAYAR DARI HP<br>
                    <span style="font-weight: normal; color: #888;">Tanpa Perlu Antre di Kasir</span>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>

