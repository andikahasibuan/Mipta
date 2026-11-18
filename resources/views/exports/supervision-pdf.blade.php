<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengawasan - {{ $month_label }}</title>
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
        <h1>Laporan Pengawasan</h1>
        <p>Periode: {{ $month_label }} &bull; Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <span>Jumlah Laporan: {{ number_format($summary['reports'], 0, ',', '.') }}</span>
        <span>Pasar Sudah Melapor: {{ number_format($summary['reported_markets'], 0, ',', '.') }}</span>
        <span>Perlu Perhatian: {{ number_format($summary['needs_attention_markets'], 0, ',', '.') }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">NO</th>
                <th style="width: 13%;">Tanggal Laporan</th>
                <th style="width: 18%;">Identitas Kontributor</th>
                <th style="width: 16%;">Pasar</th>
                <th style="width: 34%;">Keterangan</th>
                <th class="text-center" style="width: 14%;">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $report)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($report->report_date)->format('d/m/Y') }}</td>
                    <td>{{ filled($report->contributor?->name) ? \Illuminate\Support\Str::title($report->contributor->name) : '—' }}</td>
                    <td>{{ filled($report->market?->name) ? \Illuminate\Support\Str::title($report->market->name) : '—' }}</td>
                    <td>{{ $report->description ?: '—' }}</td>
                    <td class="text-center">{{ $report->condition->label() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada laporan pengawasan pada periode ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>