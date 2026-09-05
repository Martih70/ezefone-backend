<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class PricingTierChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Pricing tier split';

    protected function getData(): array
    {
        $earlyAdopter = User::where('pricing_tier', 'early_adopter')->count();
        $standard = User::where('pricing_tier', 'standard')->count();
        $unpaid = User::whereNull('pricing_tier')->count();

        return [
            'datasets' => [
                [
                    'data' => [$earlyAdopter, $standard, $unpaid],
                    'backgroundColor' => ['#f59e0b', '#059669', '#94a3b8'],
                ],
            ],
            'labels' => ['Early Adopter', 'Standard', 'Unpaid'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
