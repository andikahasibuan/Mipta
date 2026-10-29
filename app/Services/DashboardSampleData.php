<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class DashboardSampleData
{
    /**
     * Distributor contoh sampai data master tersedia.
     *
     * @var array<string, string>
     */
    public const DISTRIBUTORS = [
        'ppi' => 'PT Perusahaan Perdagangan Indonesia (PPI)',
        'rni' => 'PT Rajawali Nusantara Indonesia (RNI)',
        'bulog' => 'Badan Urusan Logistik (Bulog)',
    ];

    /**
     * Pasar contoh sampai data master tersedia.
     *
     * @var array<string, string>
     */
    public const MARKETS = [
        'pasar-raya' => 'Pasar Raya',
        'pasar-alai' => 'Pasar Alai',
        'pasar-siteba' => 'Pasar Siteba',
        'pasar-lubuk-buaya' => 'Pasar Lubuk Buaya',
        'pasar-tanjung-aura' => 'Pasar Tanjung Aura',
        'pasar-belimbing' => 'Pasar Belimbing',
        'pasar-kurao' => 'Pasar Kurao',
        'pasar-bandar-buat' => 'Pasar Bandar Buat',
        'pasar-pauh' => 'Pasar Pauh',
    ];

    /**
     * @var array<int, string>
     */
    private const MONTH_NAMES = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Ringkasan penyaluran satu bulan, opsional difilter per pasar/distributor.
     *
     * @return array{
     *     month: string,
     *     month_label: string,
     *     cartons: int,
     *     reports: int,
     *     reports_change: float|null,
     *     reports_change_direction: string,
     *     active_distributors: int,
     *     total_distributors: int,
     *     monitored_markets: int,
     *     total_markets: int,
     *     coverage_percent: int
     * }
     */
    public function summary(string $month, ?string $market = null, ?string $distributor = null): array
    {
        $distributors = $this->resolveFilter(self::DISTRIBUTORS, $distributor);
        $markets = $this->resolveFilter(self::MARKETS, $market);

        $current = $this->aggregate($month, $distributors, $markets);
        $previous = $this->aggregate($this->previousMonth($month), $distributors, $markets);

        $reportsChange = $previous['reports'] > 0
            ? round((($current['reports'] - $previous['reports']) / $previous['reports']) * 100, 1)
            : null;

        return [
            'month' => $month,
            'month_label' => $this->monthLabel($month),
            'cartons' => $current['cartons'],
            'reports' => $current['reports'],
            'reports_change' => $reportsChange,
            'reports_change_direction' => match (true) {
                $reportsChange === null => 'none',
                $reportsChange > 0 => 'up',
                $reportsChange < 0 => 'down',
                default => 'flat',
            },
            'active_distributors' => $current['active_distributors'],
            'total_distributors' => count($distributors),
            'monitored_markets' => $current['monitored_markets'],
            'total_markets' => count($markets),
            'coverage_percent' => count($markets) > 0
                ? (int) round(($current['monitored_markets'] / count($markets)) * 100)
                : 0,
        ];
    }

    /**
     * Total penyaluran per distributor pada satu bulan, terurut menurun.
     *
     * @return array<int, array{name: string, cartons: int}>
     */
    public function distributorBreakdown(string $month, ?string $market = null, ?string $distributor = null): array
    {
        $distributors = $this->resolveFilter(self::DISTRIBUTORS, $distributor);
        $markets = $this->resolveFilter(self::MARKETS, $market);
        $days = $this->startOfMonth($month)->daysInMonth;

        $breakdown = [];

        foreach ($distributors as $distributorId => $distributorName) {
            $cartons = 0;

            for ($day = 1; $day <= $days; $day++) {
                foreach ($markets as $marketId => $marketName) {
                    $cartons += $this->dailyCartons($month, $day, $distributorId, $marketId);
                }
            }

            $breakdown[] = [
                'name' => $distributorName,
                'cartons' => $cartons,
            ];
        }

        usort($breakdown, fn (array $left, array $right): int => $right['cartons'] <=> $left['cartons']);

        return $breakdown;
    }

    /**
     * Seri penyaluran harian untuk satu bulan, mengikuti filter aktif.
     *
     * @return array<int, int>
     */
    public function dailySeries(string $month, ?string $market = null, ?string $distributor = null): array
    {
        $distributors = $this->resolveFilter(self::DISTRIBUTORS, $distributor);
        $markets = $this->resolveFilter(self::MARKETS, $market);
        $days = $this->startOfMonth($month)->daysInMonth;

        $series = [];

        for ($day = 1; $day <= $days; $day++) {
            $total = 0;

            foreach ($distributors as $distributorId => $distributorName) {
                foreach ($markets as $marketId => $marketName) {
                    $total += $this->dailyCartons($month, $day, $distributorId, $marketId);
                }
            }

            $series[] = $total;
        }

        return $series;
    }

    /**
     * Label tanggal ringkas untuk sumbu grafik, mis. "1 Okt".
     *
     * @return array<int, string>
     */
    public function dayLabels(string $month): array
    {
        $date = $this->startOfMonth($month);
        $monthShort = mb_substr(self::MONTH_NAMES[$date->month], 0, 3);

        return array_map(
            fn (int $day): string => $day.' '.$monthShort,
            range(1, $date->daysInMonth),
        );
    }

    /**
     * Daftar bulan terakhir untuk pilihan filter.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function months(int $count = 6): array
    {
        $months = [];
        $cursor = Carbon::now()->startOfMonth();

        for ($index = 0; $index < $count; $index++) {
            $value = $cursor->format('Y-m');

            $months[] = [
                'value' => $value,
                'label' => $this->periodLabel($value),
            ];

            $cursor = $cursor->subMonthNoOverflow();
        }

        return $months;
    }

    /**
     * Label rentang satu bulan, mis. "01 Oktober – 31 Oktober 2026".
     */
    public function periodLabel(string $month): string
    {
        $date = $this->startOfMonth($month);

        return sprintf(
            '%02d %s – %02d %s %d',
            $date->day,
            self::MONTH_NAMES[$date->month],
            $date->daysInMonth,
            self::MONTH_NAMES[$date->month],
            $date->year,
        );
    }

    /**
     * Label bulan dalam bahasa Indonesia, mis. "Oktober 2026".
     */
    public function monthLabel(string $month): string
    {
        $date = $this->startOfMonth($month);

        return self::MONTH_NAMES[$date->month].' '.$date->year;
    }

    /**
     * @param  array<string, string>  $distributors
     * @param  array<string, string>  $markets
     * @return array{cartons: int, reports: int, active_distributors: int, monitored_markets: int}
     */
    private function aggregate(string $month, array $distributors, array $markets): array
    {
        $days = $this->startOfMonth($month)->daysInMonth;
        $cartons = 0;
        $reports = 0;
        $activeDistributors = 0;

        /** @var array<string, int> $marketTotals */
        $marketTotals = array_fill_keys(array_keys($markets), 0);

        foreach ($distributors as $distributorId => $distributorName) {
            $distributorTotal = 0;

            for ($day = 1; $day <= $days; $day++) {
                $dayTotal = 0;

                foreach ($markets as $marketId => $marketName) {
                    $value = $this->dailyCartons($month, $day, $distributorId, $marketId);

                    $dayTotal += $value;
                    $marketTotals[$marketId] += $value;
                }

                if ($dayTotal > 0) {
                    $reports++;
                }

                $distributorTotal += $dayTotal;
            }

            $cartons += $distributorTotal;

            if ($distributorTotal > 0) {
                $activeDistributors++;
            }
        }

        return [
            'cartons' => $cartons,
            'reports' => $reports,
            'active_distributors' => $activeDistributors,
            'monitored_markets' => count(array_filter($marketTotals)),
        ];
    }

    /**
     * @param  array<string, string>  $options
     * @return array<string, string>
     */
    private function resolveFilter(array $options, ?string $key): array
    {
        return $key === null
            ? $options
            : array_intersect_key($options, [$key => true]);
    }

    /**
     * Nilai penyaluran harian yang deterministik (selalu sama untuk kunci yang sama).
     */
    private function dailyCartons(string $month, int $day, string $distributor, string $market): int
    {
        $seed = crc32($month.'|'.$day.'|'.$distributor.'|'.$market);

        return 20 + $seed % 90;
    }

    private function previousMonth(string $month): string
    {
        return $this->startOfMonth($month)->subMonthNoOverflow()->format('Y-m');
    }

    private function startOfMonth(string $month): Carbon
    {
        return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    }
}
