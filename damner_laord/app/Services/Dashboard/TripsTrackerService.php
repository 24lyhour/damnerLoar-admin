<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Carbon;

/**
 * Builds the data for the Dashboard > Trips (tracker) tab: today's departures.
 *
 * Sample figures until the Transport module has real tables. The array shape
 * matches the `TripsTracker` type in resources/js/types/dashboard.ts.
 */
class TripsTrackerService
{
    /**
     * @return array<string, mixed>
     */
    public function tracker(): array
    {
        return [
            'generatedAt' => Carbon::now()->toIso8601String(),
            'kpis' => [
                ['key' => 'trips_today', 'label' => 'Trips today', 'value' => 186, 'format' => 'number', 'hint' => '42 still to depart'],
                ['key' => 'on_the_road', 'label' => 'On the road now', 'value' => 23, 'format' => 'number', 'hint' => 'GPS from 19 buses'],
                ['key' => 'on_time', 'label' => 'On-time departures', 'value' => 94, 'format' => 'percent', 'change' => 2, 'changeUnit' => 'points', 'changeLabel' => 'last 7 days 92%'],
                ['key' => 'delayed', 'label' => 'Delayed', 'value' => 2, 'format' => 'number', 'hint' => 'customers notified'],
                ['key' => 'cancelled', 'label' => 'Cancelled', 'value' => 1, 'format' => 'number', 'hint' => 'all refunded 100%'],
            ],
            'alerts' => [
                ['key' => 't1', 'time' => '20:41', 'title' => 'Giant Ibis 21:30 PP → SR delayed 20 min', 'description' => 'Bus late from depot · 22 customers got SMS', 'tone' => 'error'],
                ['key' => 't2', 'time' => '20:30', 'title' => 'Ekareach 20:45 Kampot → PP · 6 of 12 boarded', 'description' => 'Departs in 15 min', 'tone' => 'neutral'],
                ['key' => 't3', 'time' => '19:10', 'title' => 'Mekong Express 22:30 cancelled', 'description' => 'Operator suspended · 100% refunds sent', 'tone' => 'error'],
                ['key' => 't4', 'time' => '18:32', 'title' => 'Capital Express 18:30 departed', 'description' => '36 / 36 boarded', 'tone' => 'success'],
            ],
            'departures' => [
                ['time' => '20:45', 'route' => 'Kampot → Phnom Penh', 'operator' => 'Ekareach Express', 'seatsSold' => 12, 'seatsTotal' => 15, 'boarded' => 6, 'roundTripPassengers' => 4, 'status' => 'boarding'],
                ['time' => '21:30', 'route' => 'Phnom Penh → Siem Reap', 'operator' => 'Giant Ibis', 'seatsSold' => 22, 'seatsTotal' => 30, 'boarded' => 0, 'roundTripPassengers' => 9, 'status' => 'delayed', 'delayMinutes' => 20],
                ['time' => '22:00', 'route' => 'Siem Reap → Phnom Penh', 'operator' => 'Capital Express', 'seatsSold' => 27, 'seatsTotal' => 30, 'boarded' => 0, 'roundTripPassengers' => 14, 'status' => 'scheduled'],
                ['time' => '23:00', 'route' => 'Phnom Penh → Battambang', 'operator' => 'Sovan Rithy', 'seatsSold' => 18, 'seatsTotal' => 40, 'boarded' => 0, 'roundTripPassengers' => 5, 'status' => 'scheduled'],
            ],
        ];
    }
}
