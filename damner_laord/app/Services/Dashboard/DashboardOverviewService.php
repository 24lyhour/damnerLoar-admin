<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Carbon;

/**
 * Builds the data for the Dashboard > Overview tab.
 *
 * Returns sample figures until the Booking, Payments and Partners modules
 * have real tables. Swap each private method for a query when ready; the
 * array shape matches the `DashboardOverview` type in resources/js/types.
 */
class DashboardOverviewService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        return [
            'generatedAt' => Carbon::now()->toIso8601String(),
            'kpis' => $this->kpis(),
            'bookingsTrend' => $this->bookingsTrend(days: 24),
            'attention' => $this->attention(),
            'latestBookings' => $this->latestBookings(),
            'busTripType' => $this->busTripType(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function kpis(): array
    {
        return [
            ['key' => 'bookings_today', 'label' => 'Bookings today', 'value' => 343, 'format' => 'number', 'change' => 8.2],
            ['key' => 'gross_sales_today', 'label' => 'Gross sales today', 'value' => 12480, 'format' => 'currency', 'change' => 5.1],
            ['key' => 'refunds_waiting', 'label' => 'Refunds waiting', 'value' => 7, 'format' => 'number', 'hint' => '2 need second approval'],
            ['key' => 'merchants_to_approve', 'label' => 'Merchants to approve', 'value' => 3, 'format' => 'number', 'hint' => 'oldest: 2 days'],
        ];
    }

    /**
     * @return array{labels: list<string>, series: list<array{label: string, data: list<int>}>}
     */
    private function bookingsTrend(int $days): array
    {
        $range = range(1, $days);

        return [
            'labels' => array_map(strval(...), $range),
            'series' => [
                ['label' => 'Bus', 'data' => array_map(fn (int $d): int => 150 + $d * 4 + ($d % 3) * 6, $range)],
                ['label' => 'Hotel', 'data' => array_map(fn (int $d): int => 45 + $d + ($d % 4) * 2, $range)],
                ['label' => 'Package', 'data' => array_map(fn (int $d): int => 10 + intdiv($d, 3), $range)],
            ],
        ];
    }

    /**
     * @return list<array{key: string, title: string, description: string, severity: string}>
     */
    private function attention(): array
    {
        return [
            ['key' => 'webhooks', 'title' => '2 payment webhooks failed', 'description' => 'PayWay · last 1 hour · auto-retry on', 'severity' => 'error'],
            ['key' => 'refunds', 'title' => '7 refunds waiting', 'description' => '2 over $100 need second approval', 'severity' => 'info'],
            ['key' => 'merchants', 'title' => '3 merchants waiting approval', 'description' => 'Giant Ibis, Kampot Riverside, +1', 'severity' => 'info'],
            ['key' => 'trip', 'title' => 'Trip 20:45 Kampot → PP · 3 seats left', 'description' => 'Ekareach Express · departs in 25 min', 'severity' => 'info'],
        ];
    }

    /**
     * @return list<array{reference: string, customer: string, service: string, amount: float, status: string}>
     */
    private function latestBookings(): array
    {
        return [
            ['reference' => 'DL-PK-2611', 'customer' => 'Sokha Chan', 'service' => 'package', 'amount' => 209.00, 'status' => 'confirmed'],
            ['reference' => 'DL-HT-2611', 'customer' => 'Sokha Chan', 'service' => 'hotel', 'amount' => 91.00, 'status' => 'confirmed'],
            ['reference' => 'DL-BS-2611-5117', 'customer' => 'Sokha Chan', 'service' => 'bus', 'amount' => 14.00, 'status' => 'awaiting_payment'],
            ['reference' => 'DL-BS-2611-4990', 'customer' => 'Vanna Ros', 'service' => 'bus', 'amount' => 24.00, 'status' => 'payment_failed'],
        ];
    }

    /**
     * @return array{total: int, slices: list<array{label: string, value: int}>, cancelledReturnRate: float}
     */
    private function busTripType(): array
    {
        return [
            'total' => 248,
            'slices' => [
                ['label' => 'One-way', 'value' => 154],
                ['label' => 'Round-trip', 'value' => 94],
            ],
            'cancelledReturnRate' => 3.1,
        ];
    }
}
