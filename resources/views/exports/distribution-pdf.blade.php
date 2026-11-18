<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penyaluran - {{ $month_label }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1e293b; }
        .header { margin-bottom: 16px; border-bottom: 2px solid #018F98; padding-bottom: 8px; }
        .header h1 { margin: 0; font-size: 16px; color: #018F98; }
        .header p { margin: 4px 0 0; font-size: 11px; color: #475569; }
        .summary { margin-bottom: 14px; }
        .summary span { display: inline-block; margin-right: 18px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background-color: #e2e8f0; font-size: 10px; text-transform: uppercase; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Penyaluran</h1>
        <p>Periode: {{ $month_label }} &bull; Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <span>Jumlah Laporan: {{ number_format($summary['reports'], 0, ',', '.') }}</span>
        <span>Total Penyaluran: {{ number_format($summary['cartons'], 0, ',', '.') }} Karton</span>
        <span>Toko Penerima: {{ number_format($summary['recipient_stores'], 0, ',', '.') }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">NO</th>
                <th style="width: 14%;">Tanggal Penyaluran</th>
                <th style="width: 22%;">Nama Toko</th>
                <th class="text-center" style="width: 15%;">Jumlah (Karton)</th>
                <th style="width: 28%;">Alamat</th>
                <th class="text-center" style="width: 16%;">Nomor NIB</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($distributions as $distribution)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($distribution->distribution_date)->format('d/m/Y') }}</td>
                    <td>{{ filled($distribution->store?->name) ? \Illuminate\Support\Str::title($distribution->store->name) : '—' }}</td>
                    <td class="text-center">{{ number_format($distribution->cartons, 0, ',', '.') }}</td>
                    <td>{{ filled($distribution->store?->alamat_lengkap) ? \Illuminate\Support\Str::title($distribution->store->alamat_lengkap) : '—' }}</td>
                    <td class="text-center">{{ $distribution->store?->nib ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada laporan penyaluran pada periode ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>