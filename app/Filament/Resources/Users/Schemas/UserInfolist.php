<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->columns(2)
                    ->components([
                        TextEntry::make('name'),
                        TextEntry::make('email')->copyable(),
                        TextEntry::make('pricing_tier')
                            ->label('Pricing tier')
                            ->badge()
                            ->placeholder('Unpaid'),
                        TextEntry::make('stripe_customer_id')
                            ->label('Stripe customer')
                            ->placeholder('—')
                            ->copyable(),
                        TextEntry::make('created_at')
                            ->label('Signed up')
                            ->dateTime(),
                        TextEntry::make('last_opened_at')
                            ->label('Last opened app')
                            ->since()
                            ->placeholder('Never'),
                    ]),

                Section::make('Check-in')
                    ->columns(2)
                    ->components([
                        IconEntry::make('checkin_enabled')->label('Enabled')->boolean(),
                        TextEntry::make('checkin_time')->label('Time')->placeholder('—'),
                        TextEntry::make('checkin_email')->label('Alert email')->placeholder('—'),
                        TextEntry::make('checkin_paused_until')->label('Paused until')->placeholder('—'),
                    ]),

                Section::make('Family safety')
                    ->columns(2)
                    ->components([
                        TextEntry::make('sos_contact_id')->label('SOS contact ID')->placeholder('—'),
                        IconEntry::make('sos_location_sharing')->label('Location sharing')->boolean(),
                    ]),
            ]);
    }
}
