<?php

namespace App\Support;

/**
 * Alt text for images inserted through the rich-text editor.
 *
 * Filament's image upload does not ask for a description, and the resulting
 * markup carries no field to store one, so descriptions are collected after
 * the fact and written back into the body's <img> tags.
 */
final class ImageDescriptions
{
    /**
     * Images in the body that have no alt text yet.
     *
     * @return list<array{src: string, tag: string}>
     */
    public static function missing(?string $html): array
    {
        $missing = [];

        foreach (self::imageTags((string) $html) as $tag) {
            if (trim(self::attribute($tag, 'alt')) !== '') {
                continue;
            }

            $missing[] = [
                'src' => self::attribute($tag, 'src') ?: 'Image',
                'tag' => $tag,
            ];
        }

        return $missing;
    }

    /**
     * Write descriptions back into the body, matching each image by src.
     *
     * @param  array<int, array{src?: string, alt?: string}>  $descriptions
     */
    public static function apply(?string $html, array $descriptions): string
    {
        $html = (string) $html;

        foreach ($descriptions as $description) {
            $src = $description['src'] ?? null;
            $alt = trim((string) ($description['alt'] ?? ''));

            if (blank($src) || $alt === '') {
                continue;
            }

            $html = preg_replace_callback(
                '/<img\b[^>]*>/i',
                function (array $match) use ($src, $alt): string {
                    if (self::attribute($match[0], 'src') !== $src) {
                        return $match[0];
                    }

                    return self::withAltText($match[0], $alt);
                },
                $html,
            ) ?? $html;
        }

        return $html;
    }

    /**
     * @return list<string>
     */
    private static function imageTags(string $html): array
    {
        preg_match_all('/<img\b[^>]*>/i', $html, $matches);

        return $matches[0];
    }

    private static function withAltText(string $tag, string $alt): string
    {
        $escaped = htmlspecialchars($alt, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (preg_match('/\balt="[^"]*"/i', $tag)) {
            return preg_replace('/\balt="[^"]*"/i', 'alt="'.$escaped.'"', $tag, 1) ?? $tag;
        }

        return preg_replace('/\s*\/?>$/', ' alt="'.$escaped.'">', $tag, 1) ?? $tag;
    }

    private static function attribute(string $tag, string $name): string
    {
        return preg_match('/\b'.$name.'="([^"]*)"/i', $tag, $match) ? $match[1] : '';
    }
}
