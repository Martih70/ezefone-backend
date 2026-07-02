<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
        'family_code_word',
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
}
