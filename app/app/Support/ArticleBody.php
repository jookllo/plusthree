<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Turns a stored article body into HTML that is safe to echo.
 *
 * The editor writes markup, so the body can no longer be escaped wholesale.
 * Everything passes through an allowlist first: formatting and images survive,
 * scripts, event handlers and javascript: URLs do not.
 */
final class ArticleBody
{
    private static ?HtmlSanitizer $sanitizer = null;

    /**
     * Render a stored body for display.
     *
     * Bodies written before the rich-text editor existed are plain text: they
     * are escaped and their line breaks preserved. Anything containing markup
     * goes through the sanitiser, which is authoritative even if a record was
     * written through tinker, a seeder or a future import.
     */
    public static function render(?string $body): HtmlString
    {
        $body = (string) $body;

        if (trim($body) === '') {
            return new HtmlString('');
        }

        if (! self::isMarkup($body)) {
            return new HtmlString(nl2br(e($body), false));
        }

        return new HtmlString(self::sanitizer()->sanitize($body));
    }

    /**
     * Strip markup down to readable text, for word counts and meta fallbacks.
     */
    public static function plainText(?string $body): string
    {
        $body = (string) $body;

        return trim(html_entity_decode(
            strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $body)),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        ));
    }

    public static function sanitizer(): HtmlSanitizer
    {
        return self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowRelativeLinks()
                ->allowRelativeMedias()
                ->allowLinkSchemes(['http', 'https', 'mailto'])
                ->allowMediaSchemes(['http', 'https'])
                ->allowAttribute('target', ['a'])
                ->allowAttribute('rel', ['a'])
                ->allowAttribute('loading', ['img'])
                ->allowAttribute('decoding', ['img'])
        );
    }

    /**
     * A body is treated as markup only when it contains an element the editor
     * or an importer could plausibly emit. Prose such as "5 < 6" stays text.
     */
    public static function isMarkup(string $body): bool
    {
        return (bool) preg_match(
            '/<\/?(?:p|br|strong|b|em|i|u|s|h[1-6]|ul|ol|li|blockquote|a|img|figure|figcaption|hr|code|pre|span|div)\b[^>]*>/i',
            $body,
        );
    }
}
