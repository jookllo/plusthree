<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('title', 180);
            $table->string('slug', 200)->unique();
            $table->longText('description');
            $table->longText('requirements')->nullable();
            $table->string('employment_type', 20);
            $table->string('work_mode', 20);
            $table->string('location_city', 120)->nullable();
            $table->string('location_country', 80)->nullable();
            $table->string('application_url', 2048)->nullable();
            $table->string('application_email', 255)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closing_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index('closing_at');
            $table->index(['company_id', 'status']);
            $table->index('employment_type');
            $table->index('work_mode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
