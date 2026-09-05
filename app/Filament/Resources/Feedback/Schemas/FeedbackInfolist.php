<?php

namespace App\Filament\Resources\Feedback\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedbackInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextEntry::make('user.name')->label('From'),
                        TextEntry::make('user.email')->label('Email')->copyable(),
                        TextEntry::make('created_at')->label('Submitted')->dateTime(),
                        IconEntry::make('emailed')->label('Notification sent')->boolean(),
                        TextEntry::make('message')
                            ->columnSpanFull()
                            ->prose(),
                    ]),
            ]);
    }
}
