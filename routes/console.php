<?php

use App\Mail\CheckinAlert;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily check-in: runs hourly, fires alert for each user whose check-in
// time has passed today, hasn't opened the app today, and isn't paused.
Schedule::call(function () {
    $now   = now();
    $today = $now->toDateString();

    User::where('checkin_enabled', true)
        ->whereNotNull('checkin_email')
        ->get()
        ->each(function (User $user) use ($now, $today) {

            // Already alerted today
            if ($user->checkin_alerted_date && $user->checkin_alerted_date->toDateString() === $today) {
                return;
            }

            // Paused for today
            if ($user->checkin_paused_until && $user->checkin_paused_until->toDateString() >= $today) {
                return;
            }

            // Not yet past the user's check-in time
            [$hour, $minute] = explode(':', $user->checkin_time ?? '11:00');
            $cutoff = $now->copy()->setTime((int) $hour, (int) $minute, 0);
            if ($now->lt($cutoff)) {
                return;
            }

            // User has already opened the app today
            if ($user->last_opened_at && $user->last_opened_at->toDateString() === $today) {
                return;
            }

            Mail::to($user->checkin_email)->send(new CheckinAlert($user));
            $user->update(['checkin_alerted_date' => $today]);
        });
})->hourly()->name('checkin-alerts')->withoutOverlapping();
