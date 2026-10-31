<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;

/**
 * Development-only sample taxonomy.
 *
 * Safe to re-run: records are matched by kind + name, and slugs are generated
 * by the model when left blank. Not run automatically in tests or production.
 */
class TaxonomySeeder extends Seeder
{
    /**
     * Job sectors. Keep this list short and stable; these become the public
     * sector filters on the job board.
     *
     * @var list<string>
     */
    private array $jobSectors = [
        'Community & social impact',
        'Design & technology',
        'Marketing & communications',
        'Research & learning',
        'Finance & operations',
        'Education & training',
        'Health & wellbeing',
    ];

    /**
     * Article categories. These become the blog's section navigation.
     *
     * @var list<string>
     */
    private array $articleCategories = [
        'CVs & applications',
        'Interviews',
        'Remote work',
        'Career growth',
        'Salary & negotiation',
        'Workplace & culture',
    ];

    /**
     * Cross-cutting tags. Deliberately excludes things already stored as
     * structured fields (employment type, work mode, country), so tags do not
     * duplicate data the filters can already use.
     *
     * @var list<string>
     */
    private array $tags = [
        'Entry level',
        'Senior',
        'Portfolio',
        'Networking',
        'Mentorship',
        'Freelance',
        'Visa sponsorship',
        'Leadership',
    ];

    public function run(): void
    {
        foreach ($this->jobSectors as $name) {
            Category::firstOrCreate([
                'kind' => Category::KIND_JOB,
                'name' => $name,
            ]);
        }

        foreach ($this->articleCategories as $name) {
            Category::firstOrCreate([
                'kind' => Category::KIND_ARTICLE,
                'name' => $name,
            ]);
        }

        foreach ($this->tags as $name) {
            Tag::firstOrCreate(['name' => $name]);
        }
    }
}
