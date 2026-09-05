<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('pricing_tier')
                    ->label('Tier')
                    ->badge()
                    ->placeholder('Unpaid')
                    ->color(fn (?string $state) => match ($state) {
                        'early_adopter' => 'warning',
                        'standard' => 'success',
                        default => 'gray',
                    }),
                IconColumn::make('checkin_enabled')
                    ->label('Check-in')
                    ->boolean(),
                TextColumn::make('last_opened_at')
                    ->label('Last opened')
                    ->since()
                    ->sortable()
                    ->placeholder('Never'),
                TextColumn::make('created_at')
                    ->label('Signed up')
                    ->dateTime('j M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('pricing_tier')
                    ->label('Tier')
                    ->options([
                        'early_adopter' => 'Early Adopter',
                        'standard' => 'Standard',
                    ])
                    ->placeholder('All'),
                Filter::make('active_last_7_days')
                    ->label('Active in last 7 days')
                    ->query(fn (Builder $query) => $query->where('last_opened_at', '>=', now()->subDays(7))),
                Filter::make('inactive_30_days')
                    ->label('Inactive 30+ days')
                    ->query(fn (Builder $query) => $query->where(function (Builder $query) {
                        $query->whereNull('last_opened_at')
                            ->orWhere('last_opened_at', '<', now()->subDays(30));
                    })),
            ])
            // Monitoring only — no bulk actions, no delete/edit from here.
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
