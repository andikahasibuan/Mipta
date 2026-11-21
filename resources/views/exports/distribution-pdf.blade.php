<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penyaluran - {{ $month_label }}</title>
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
    <h1>Laporan Penyaluran MinyaKita</h1>
    <p class="period">Periode: {{ $month_label }}</p>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">NO</th>
                <th class="text-center" style="width: 12%;">Tanggal Penyaluran</th>
                <th class="text-center" style="width: 18%;">Nama Toko</th>
                <th class="text-center" style="width: 12%;">Jumlah (Karton)</th>
                <th class="text-center" style="width: 32%;">Alamat</th>
                <th class="text-center" style="width: 16%;">Nomor NIB</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($distributions as $distribution)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ \Illuminate\Support\Carbon::parse($distribution->distribution_date)->format('d/m/Y') }}</td>
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
