<?php

use App\Support\ArticleBody;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Article bodies written before the rich-text editor are plain text with
 * blank-line paragraphs. The editor reads markup, so those newlines would
 * collapse the first time an article is opened. Convert them once, here,
 * rather than hoping nobody edits an old draft.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('articles')
            ->select('id', 'body')
            ->whereNotNull('body')
            ->orderBy('id')
            ->chunk(200, function ($articles): void {
                foreach ($articles as $article) {
                    $html = self::toParagraphs($article->body);

                    if ($html !== $article->body) {
                        DB::table('articles')->where('id', $article->id)->update(['body' => $html]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Irreversible by design: the original plain text and the converted
        // markup are not distinguishable once editors have made changes.
    }

    private static function toParagraphs(string $body): string
    {
        if (trim($body) === '' || ArticleBody::isMarkup($body)) {
            return $body;
        }

        $paragraphs = preg_split('/\n\s*\n/', trim($body)) ?: [];

        return implode("\n", array_map(
            fn (string $paragraph): string => '<p>'.nl2br(e(trim($paragraph)), false).'</p>',
            $paragraphs,
        ));
    }
};
