<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emails', function (Blueprint $table): void {
            // Null means "use the default MAIL_FROM_ADDRESS". Only addresses
            // listed in config('mail.senders') are honoured at send time.
            $table->string('from_address', 190)->nullable()->after('subject');
        });
    }

    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table): void {
            $table->dropColumn('from_address');
        });
    }
};
