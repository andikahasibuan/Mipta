<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengawasan - {{ $month_label }}</title>
    <style>
        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: normal;
            src: url('{{ str_replace('\\', '/', resource_path('fonts/PlusJakartaSans-Regular.ttf')) }}') format('truetype');
        }

        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: bold;
            src: url('{{ str_replace('\\', '/', resource_path('fonts/PlusJakartaSans-Bold.ttf')) }}') format('truetype');
        }

        @page {
            margin: 10mm;
        }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .period {
            margin: 4px 0 12px;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        th,
        td {
            padding: 5px 6px;
            border: 1px solid #000;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #fff;
            font-size: 8px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        tr {
            page-break-inside: avoid;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Laporan Pengawasan MinyaKita</h1>
    <p class="period">Periode: {{ $month_label }}</p>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">NO</th>
                <th class="text-center" style="width: 12%;">Tanggal Laporan</th>
                <th class="text-center" style="width: 18%;">Identitas Kontributor</th>
                <th class="text-center" style="width: 18%;">Pasar</th>
                <th class="text-center" style="width: 33%;">Keterangan</th>
                <th class="text-center" style="width: 14%;">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $report)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ \Illuminate\Support\Carbon::parse($report->report_date)->format('d/m/Y') }}</td>
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
