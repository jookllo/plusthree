<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * In-body images are stored as files with no database record pointing at them,
 * so removing an image from an article leaves the file behind. This finds
 * files under articles/inline that no article references any more.
 *
 * Dry run by default: deletion needs --force, and files newer than the grace
 * period are never touched, so an image uploaded moments ago cannot be lost.
 */
class PruneInlineImages extends Command
{
    protected $signature = 'images:prune
        {--force : Delete the files instead of listing them}
        {--hours=24 : Ignore files modified more recently than this}';

    protected $description = 'Report or delete in-body images that no article references';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $referenced = $this->referencedPaths();
        $cutoff = now()->subHours(max(0, (int) $this->option('hours')))->getTimestamp();

        $orphans = collect($disk->files('articles/inline'))
            ->reject(fn (string $file): bool => isset($referenced[$file]))
            ->filter(fn (string $file): bool => $disk->lastModified($file) <= $cutoff);

        if ($orphans->isEmpty()) {
            $this->info('No orphaned in-body images.');

            return self::SUCCESS;
        }

        foreach ($orphans as $file) {
            if ($this->option('force')) {
                $disk->delete($file);
                $this->line("Deleted {$file}");
            } else {
                $this->line("Would delete {$file}");
            }
        }

        $this->info($this->option('force')
            ? sprintf('Deleted %d orphaned image(s).', $orphans->count())
            : sprintf('Found %d orphaned image(s). Re-run with --force to delete.', $orphans->count()));

        return self::SUCCESS;
    }

    /**
     * Every path mentioned in any article body, matched loosely so that both
     * relative and absolute URLs count as a reference.
     *
     * @return array<string, true>
     */
    private function referencedPaths(): array
    {
        $paths = [];

        Article::query()
            ->select('body')
            ->whereNotNull('body')
            ->cursor()
            ->each(function (Article $article) use (&$paths): void {
                preg_match_all('/articles\/inline\/[^\s"\'<>?\\\\]+/i', (string) $article->body, $matches);

                foreach ($matches[0] as $path) {
                    $paths[rawurldecode($path)] = true;
                }
            });

        return $paths;
    }
}
