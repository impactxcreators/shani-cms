<?php

namespace App\Filament\Widgets;

use App\Models\ClickEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ClicksChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Clicks — last 14 days';

    protected function getData(): array
    {
        $labels = [];
        $values = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $labels[] = $day->format('M j');
            $values[] = ClickEvent::whereBetween('created_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])->count();
        }

        return [
            'datasets' => [[
                'label' => 'Clicks',
                'data' => $values,
                'borderColor' => '#f59e0b',
                'backgroundColor' => 'rgba(245,158,11,0.2)',
                'fill' => true,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
