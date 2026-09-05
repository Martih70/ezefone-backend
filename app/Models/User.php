<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'stripe_customer_id',
        'pricing_tier',
        'sos_contact_id',
        'sos_location_sharing',
        'last_opened_at',
        'checkin_enabled',
        'checkin_time',
        'checkin_email',
        'checkin_paused_until',
        'checkin_alerted_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'sos_location_sharing' => 'boolean',
            'last_opened_at'       => 'datetime',
            'checkin_enabled'      => 'boolean',
            'checkin_paused_until' => 'date',
            'checkin_alerted_date' => 'date',
        ];
    }

    /**
     * Gate for the /backoffice Filament panel — reuses the same
     * ADMIN_EMAILS allowlist as the existing hand-rolled /admin/* pages
     * (see EnsureUserIsAdmin) rather than a separate role system.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->email, config('app.admin_emails', []), true);
    }
}
