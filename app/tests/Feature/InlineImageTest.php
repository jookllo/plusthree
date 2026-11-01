<?php

namespace Tests\Feature;

use App\Support\InlineImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InlineImageTest extends TestCase
{
    public function test_uploads_are_stored_on_the_public_disk(): void
    {
        Storage::fake('public');

        $path = InlineImage::store(UploadedFile::fake()->create('photo.jpg', 40, 'image/jpeg'));

        $this->assertStringStartsWith('articles/inline/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_a_wide_image_is_downscaled_to_the_maximum_width(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD is not available in this environment.');
        }

        Storage::fake('public');

        $source = imagecreatetruecolor(3000, 2000);
        imagejpeg($source, Storage::disk('public')->path('articles/inline/wide.jpg'), 90);
        imagedestroy($source);

        $this->assertTrue(InlineImage::downscale('public', 'articles/inline/wide.jpg'));

        $size = getimagesize(Storage::disk('public')->path('articles/inline/wide.jpg'));

        $this->assertSame(InlineImage::MAX_WIDTH, $size[0]);
        $this->assertSame((int) round(2000 * (InlineImage::MAX_WIDTH / 3000)), $size[1]);
    }

    public function test_an_image_within_the_limit_is_left_untouched(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD is not available in this environment.');
        }

        Storage::fake('public');

        $source = imagecreatetruecolor(800, 600);
        imagejpeg($source, Storage::disk('public')->path('articles/inline/small.jpg'), 90);
        imagedestroy($source);

        $before = md5_file(Storage::disk('public')->path('articles/inline/small.jpg'));

        $this->assertFalse(InlineImage::downscale('public', 'articles/inline/small.jpg'));
        $this->assertSame($before, md5_file(Storage::disk('public')->path('articles/inline/small.jpg')));
    }

    public function test_unreadable_files_are_left_in_place(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('articles/inline/broken.jpg', 'not an image');

        $this->assertFalse(InlineImage::downscale('public', 'articles/inline/broken.jpg'));
        Storage::disk('public')->assertExists('articles/inline/broken.jpg');
    }
}
