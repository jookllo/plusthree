<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 190);
            $table->string('company', 160)->nullable();
            $table->string('role_title', 160)->nullable();
            $table->string('employment_type', 20)->nullable();
            $table->string('link', 500)->nullable();
            $table->text('body');
            $table->string('source', 40)->default('employer_form')->index();
            $table->string('status', 20)->default('new')->index();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
