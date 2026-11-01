<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Images inserted through the rich-text editor.
 *
 * The editor uploads whatever the browser sends, so a phone photo arrives at
 * several thousand pixels and several megabytes. Anything wider than
 * MAX_WIDTH is re-encoded down. Every failure path keeps the original file:
 * losing size is acceptable, losing the image is not.
 */
final class InlineImage
{
    public const MAX_WIDTH = 1400;

    private const QUALITY = 82;

    public static function store(UploadedFile $file, string $directory = 'articles/inline', string $disk = 'public'): string
    {
        $path = $file->store($directory, ['disk' => $disk, 'visibility' => 'public']);

        if (is_string($path)) {
            self::downscale($disk, $path);
        }

        return (string) $path;
    }

    public static function downscale(string $disk, string $path): bool
    {
        if (! extension_loaded('gd')) {
            return false;
        }

        $storage = Storage::disk($disk);

        try {
            $full = $storage->path($path);
        } catch (\Throwable) {
            return false;
        }

        $contents = @file_get_contents($full);

        if ($contents === false) {
            return false;
        }

        $source = @imagecreatefromstring($contents);

        if ($source === false) {
            return false;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width <= self::MAX_WIDTH) {
            imagedestroy($source);

            return false;
        }

        $targetWidth = self::MAX_WIDTH;
        $targetHeight = max(1, (int) round($height * (self::MAX_WIDTH / $width)));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($source);

        $temporary = $full.'.tmp';
        $encoded = self::encode($target, $temporary, pathinfo($path, PATHINFO_EXTENSION));
        imagedestroy($target);

        if (! $encoded || ! @rename($temporary, $full)) {
            @unlink($temporary);

            return false;
        }

        return true;
    }

    private static function encode(\GdImage $image, string $path, string $extension): bool
    {
        return match (strtolower($extension)) {
            'jpg', 'jpeg' => imagejpeg($image, $path, self::QUALITY),
            'png' => imagepng($image, $path, 8),
            'webp' => function_exists('imagewebp') && imagewebp($image, $path, self::QUALITY),
            default => false,
        };
    }
}
