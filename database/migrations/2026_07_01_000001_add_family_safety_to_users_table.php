<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('family_code_word')->nullable()->after('stripe_customer_id');
            $table->foreignId('sos_contact_id')->nullable()->after('family_code_word')
                  ->constrained('contacts')->nullOnDelete();
            $table->boolean('sos_location_sharing')->default(false)->after('sos_contact_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sos_contact_id']);
            $table->dropColumn(['family_code_word', 'sos_contact_id', 'sos_location_sharing']);
        });
    }
};
