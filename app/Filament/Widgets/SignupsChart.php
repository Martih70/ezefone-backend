<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class SignupsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = ['md' => 2];

    protected ?string $heading = 'Signups (last 30 days)';

    protected function getData(): array
    {
        $days = 30;
        $start = now()->subDays($days - 1)->startOfDay();

        // Grouped in PHP rather than a DB-specific date() function, so this
        // works the same on sqlite (local) and whatever production uses.
        $counts = User::where('created_at', '>=', $start)
            ->pluck('created_at')
            ->countBy(fn ($date) => $date->format('Y-m-d'));

        $labels = [];
        $data = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $labels[] = $date->format('j M');
            $data[] = $counts->get($date->format('Y-m-d'), 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Signups',
                    'data' => $data,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
