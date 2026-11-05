<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jobs get the same editorial fields as articles: a featured image with alt
 * text, and SEO overrides. Description and requirements were already text
 * columns, so they can hold the rich-text editor's markup without a change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table): void {
            $table->string('featured_image_path', 255)->nullable()->after('requirements');
            $table->string('featured_image_alt', 255)->nullable()->after('featured_image_path');
            $table->string('seo_title', 255)->nullable()->after('closing_at');
            $table->string('seo_description', 320)->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table): void {
            $table->dropColumn([
                'featured_image_path',
                'featured_image_alt',
                'seo_title',
                'seo_description',
            ]);
        });
    }
};
