<?php

namespace App\Filament\Resources\Feedback\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FeedbackTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('From')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('message')
                    ->limit(80)
                    ->wrap(),
                IconColumn::make('emailed')
                    ->label('Notified')
                    ->boolean()
                    ->tooltip('Whether the notification email to info@ezefone.co.uk succeeded'),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('j M Y, g:ia')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('emailed')
                    ->label('Notification status')
                    ->trueLabel('Notified')
                    ->falseLabel('Failed to notify (check mail config)')
                    ->placeholder('All'),
            ])
            // Read-only — feedback is submitted by users, never edited here.
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
