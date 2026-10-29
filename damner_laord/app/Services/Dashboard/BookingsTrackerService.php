<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Carbon;

/**
 * Builds the data for the Dashboard > Bookings (tracker) tab.
 *
 * Sample figures until the Booking module has real tables. The array shape
 * matches the `BookingsTracker` type in resources/js/types/dashboard.ts.
 */
class BookingsTrackerService
{
    /**
     * @return array<string, mixed>
     */
    public function tracker(): array
    {
        return [
            'generatedAt' => Carbon::now()->toIso8601String(),
            'kpis' => $this->kpis(),
            'funnel' => $this->funnel(),
            'activity' => $this->activity(),
            'byService' => [
                ['label' => 'Bus', 'value' => 248],
                ['label' => 'Hotel', 'value' => 73],
                ['label' => 'Package', 'value' => 22],
            ],
            'busTripType' => [
                'total' => 248,
                'slices' => [
                    ['label' => 'One-way', 'value' => 154],
                    ['label' => 'Round-trip', 'value' => 94],
                ],
            ],
            'activeHolds' => [
                ['reference' => 'DL-BS-2611-5231', 'item' => 'PP → SR · 2 seats', 'secondsLeft' => 102],
                ['reference' => 'DL-HT-2611-0390', 'item' => 'Angkor Paradise · 1 room', 'secondsLeft' => 370],
                ['reference' => 'DL-PK-2611-0413', 'item' => 'Package · 3 parts', 'secondsLeft' => 535],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function kpis(): array
    {
        return [
            ['key' => 'searches', 'label' => 'Searches', 'value' => 9820, 'format' => 'number', 'change' => 6, 'changeLabel' => 'vs last Sat'],
            ['key' => 'active_holds', 'label' => 'Active holds', 'value' => 46, 'format' => 'number', 'hint' => 'seats + rooms right now'],
            ['key' => 'paid_bookings', 'label' => 'Paid bookings', 'value' => 343, 'format' => 'number', 'change' => 8.2, 'changeLabel' => 'vs last Sat'],
            ['key' => 'conversion', 'label' => 'Conversion', 'value' => 3.5, 'format' => 'percent', 'change' => 0.3, 'changeUnit' => 'points', 'changeLabel' => 'search → paid'],
            ['key' => 'round_trip_share', 'label' => 'Round-trip share', 'value' => 38, 'format' => 'percent', 'change' => 4, 'changeUnit' => 'points', 'changeLabel' => 'of bus bookings'],
        ];
    }

    /**
     * @return array{steps: list<array{label: string, value: int, note?: string}>, insight: string}
     */
    private function funnel(): array
    {
        $steps = [
            'Searched' => 9820,
            'Viewed results' => 7410,
            'Selected seat / room' => 2050,
            'Started checkout' => 1412,
            'Started payment' => 690,
            'Paid' => 343,
        ];

        $previous = null;
        $rows = [];

        foreach ($steps as $label => $value) {
            $row = ['label' => $label, 'value' => $value];

            if ($previous !== null) {
                $row['note'] = round($value / $previous * 100).'% of previous';
            }

            $rows[] = $row;
            $previous = $value;
        }

        return [
            'steps' => $rows,
            'insight' => 'Biggest drop: selected → checkout (login required). 214 holds expired without payment.',
        ];
    }

    /**
     * @return list<array{key: string, time: string, title: string, description: string, tone: string}>
     */
    private function activity(): array
    {
        return [
            ['key' => 'a1', 'time' => '10:42', 'title' => 'DL-BS-2611-5230 paid $25.00', 'description' => 'Bus · Siem Reap → PP · one-way · KHQR', 'tone' => 'success'],
            ['key' => 'a2', 'time' => '10:41', 'title' => 'Hold expired · 2 seats released', 'description' => 'Capital Express 13:00 · SR → PP', 'tone' => 'neutral'],
            ['key' => 'a3', 'time' => '10:40', 'title' => 'DL-PK-2611-0412 paid $186.00', 'description' => 'Package · PP ⇄ Kampot · round-trip', 'tone' => 'success'],
            ['key' => 'a4', 'time' => '10:39', 'title' => 'Payment failed DL-BS-2611-5229 · $24.00', 'description' => 'ABA Mobile · declined by bank', 'tone' => 'error'],
            ['key' => 'a5', 'time' => '10:38', 'title' => 'DL-HT-2611-0388 paid $78.00', 'description' => 'Hotel · Sokha Angkor Resort · 1 night', 'tone' => 'success'],
        ];
    }
}
