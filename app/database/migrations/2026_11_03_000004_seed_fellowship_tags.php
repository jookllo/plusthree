<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Two starting tags for the taxonomy, so the panel's tag picker has
 * Fellowship and Other opportunities without anyone typing them first.
 *
 * A tag is only a cross-cutting label; the Contract type field remains the
 * structured value the public filters and landing pages use.
 */
return new class extends Migration
{
    /**
     * @var list<array{name: string, slug: string}>
     */
    private array $tags = [
        ['name' => 'Fellowship', 'slug' => 'fellowship'],
        ['name' => 'Other opportunities', 'slug' => 'other-opportunities'],
    ];

    public function up(): void
    {
        foreach ($this->tags as $tag) {
            $exists = DB::table('tags')->where('slug', $tag['slug'])->exists();

            if (! $exists) {
                DB::table('tags')->insert([
                    'name' => $tag['name'],
                    'slug' => $tag['slug'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Deliberately non-destructive: a tag may already be attached to
        // published content by the time this is rolled back.
    }
};
