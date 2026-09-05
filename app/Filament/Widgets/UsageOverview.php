<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UsageOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $paidUsers = User::whereNotNull('pricing_tier')->count();

        return [
            Stat::make('Total users', $totalUsers),

            Stat::make('Paid users', $paidUsers)
                ->description($totalUsers > 0
                    ? number_format($paidUsers / $totalUsers * 100, 1).'% of total'
                    : null),

            Stat::make('Active today', User::where('last_opened_at', '>=', now()->startOfDay())->count())
                ->description('Opened the app today'),

            Stat::make('Active this week', User::where('last_opened_at', '>=', now()->subDays(7))->count())
                ->description('Opened the app in the last 7 days'),

            Stat::make('New signups this week', User::where('created_at', '>=', now()->subDays(7))->count()),

            Stat::make('Feedback this week', Feedback::where('created_at', '>=', now()->subDays(7))->count()),
        ];
    }
}
