<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Carbon;

/**
 * Builds the data for the Dashboard > Finance (tracker) tab: month to date.
 *
 * Sample figures until the Payments / Finance modules have real tables. The
 * array shape matches the `FinanceTracker` type in resources/js/types/dashboard.ts.
 */
class FinanceTrackerService
{
    /**
     * @return array<string, mixed>
     */
    public function tracker(): array
    {
        $from = Carbon::now()->startOfMonth();
        $to = Carbon::now();

        return [
            'period' => [
                'label' => $from->format('F Y'),
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'kpis' => $this->kpis(),
            'cashFlow' => $this->cashFlow(days: 24),
            'moneyFlow' => $this->moneyFlow(),
            'revenueByService' => $this->revenueByService(),
            'settlements' => [
                ['date' => '26 Oct', 'flow' => 'PayWay → us', 'amount' => 12480.00, 'status' => 'expected'],
                ['date' => '27 Oct', 'flow' => 'Us → merchants', 'amount' => 55183.00, 'status' => 'ready'],
                ['date' => '23 Oct', 'flow' => 'PayWay → us', 'amount' => 11906.00, 'status' => 'settled'],
                ['date' => '20 Oct', 'flow' => 'Us → merchants', 'amount' => 51904.00, 'status' => 'paid'],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function kpis(): array
    {
        return [
            ['key' => 'gross_sales', 'label' => 'Gross sales', 'value' => 298420, 'format' => 'currency', 'change' => 12, 'changeLabel' => 'vs Sep (same days)'],
            ['key' => 'platform_revenue', 'label' => 'Platform revenue', 'value' => 41280, 'format' => 'currency', 'change' => 9, 'changeLabel' => 'commission + fees − PayWay fees'],
            ['key' => 'refunds', 'label' => 'Refunds', 'value' => 6940, 'format' => 'currency', 'hint' => '2.3% of sales'],
            ['key' => 'payway_unsettled', 'label' => 'PayWay not yet settled', 'value' => 18640, 'format' => 'currency', 'hint' => 'T+1 · arrives Mon'],
            ['key' => 'owed_to_merchants', 'label' => 'Owed to merchants', 'value' => 55183, 'format' => 'currency', 'hint' => 'payout Tue 27 Oct'],
        ];
    }

    /**
     * Daily money in vs money out; payouts make the weekly spikes.
     *
     * @return array{labels: list<string>, series: list<array{label: string, data: list<int>}>}
     */
    private function cashFlow(int $days): array
    {
        $range = range(1, $days);

        return [
            'labels' => array_map(strval(...), $range),
            'series' => [
                ['label' => 'Money in (sales)', 'data' => array_map(fn (int $d): int => 8000 + $d * 180 + ($d % 3) * 400, $range)],
                ['label' => 'Money out (refunds + payouts)', 'data' => array_map(fn (int $d): int => $d % 5 === 3 ? 9000 : 700 + ($d % 4) * 150, $range)],
            ],
        ];
    }

    /**
     * @return array{lines: list<array{label: string, amount: float}>, net: array{label: string, amount: float}, note: string}
     */
    private function moneyFlow(): array
    {
        return [
            'lines' => [
                ['label' => 'Gross sales', 'amount' => 298420.00],
                ['label' => 'PayWay fees', 'amount' => -4476.00],
                ['label' => 'Refunds', 'amount' => -6940.00],
                ['label' => 'Merchant share', 'amount' => -245724.00],
            ],
            'net' => ['label' => 'Platform net revenue', 'amount' => 41280.00],
            'note' => 'Commission $39,040 + service fees $6,716 − PayWay fees $4,476',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function revenueByService(): array
    {
        return [
            ['service' => 'bus', 'bookings' => 5084, 'gross' => 186200.00, 'commission' => 22344.00, 'commissionRate' => 12, 'refunds' => 3910.00, 'roundTripShare' => 38],
            ['service' => 'hotel', 'bookings' => 1402, 'gross' => 98640.00, 'commission' => 14796.00, 'commissionRate' => 15, 'refunds' => 2280.00, 'roundTripShare' => null],
            ['service' => 'package', 'bookings' => 230, 'gross' => 13580.00, 'commission' => 1900.00, 'commissionRate' => 14, 'refunds' => 750.00, 'roundTripShare' => 100],
        ];
    }
}
