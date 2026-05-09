<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Active Users', User::where('account_status', 'active')->count())
                ->description('Users with active accounts')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Inactive Users', User::where('account_status', 'inactive')->count())
                ->description('Users with inactive accounts')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            Stat::make('Total Projects', Project::count())
                ->description('All projects created')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info'),
        ];
    }
}
