<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 10);
            $table->string('name', 80);
            $table->string('slug', 100);
            $table->timestamps();

            $table->unique(['kind', 'slug']);
            $table->index('kind');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
