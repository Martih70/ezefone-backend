<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_opened_at')->nullable()->after('sos_location_sharing');
            $table->boolean('checkin_enabled')->default(false)->after('last_opened_at');
            $table->string('checkin_time', 5)->default('11:00')->after('checkin_enabled');
            $table->string('checkin_email')->nullable()->after('checkin_time');
            $table->date('checkin_paused_until')->nullable()->after('checkin_email');
            $table->date('checkin_alerted_date')->nullable()->after('checkin_paused_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_opened_at',
                'checkin_enabled',
                'checkin_time',
                'checkin_email',
                'checkin_paused_until',
                'checkin_alerted_date',
            ]);
        });
    }
};
