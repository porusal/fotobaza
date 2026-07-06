<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\Photo;
use App\Support\GalleryFilesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryFilesystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_physical_directory_for_admin_gallery(): void
    {
        Storage::fake('public');

        $gallery = Gallery::create([
            'name' => 'portfolio',
            'display_name' => 'Portfolio',
            'slug' => 'portfolio',
            'is_active' => true,
        ]);

        $directory = app(GalleryFilesystem::class)->ensureDirectoryForGallery($gallery);

        $this->assertSame('photos/portfolio', $directory);
        $this->assertDirectoryExists(Storage::disk('public')->path('photos/portfolio'));
    }

    public function test_it_moves_gallery_directory_under_new_parent_and_updates_photo_paths(): void
    {
        Storage::fake('public');

        $parent = Gallery::create([
            'name' => 'portfolio',
            'display_name' => 'Portfolio',
            'slug' => 'portfolio',
            'is_active' => true,
        ]);

        $gallery = Gallery::create([
            'name' => 'weddings',
            'display_name' => 'Weddings',
            'slug' => 'weddings',
            'is_active' => true,
        ]);

        Storage::disk('public')->put('photos/weddings/photo-01.jpg', 'image');

        Photo::create([
            'gallery_id' => $gallery->id,
            'filename' => 'photo-01.jpg',
            'path' => '/storage/photos/weddings/photo-01.jpg',
            'alt_text' => 'Wedding',
        ]);

        $filesystem = app(GalleryFilesystem::class);
        $oldDirectory = $filesystem->directoryForGallery($gallery);

        $gallery->parent_id = $parent->id;
        $gallery->save();

        $newDirectory = $filesystem->moveDirectoryForGallery($gallery->refresh(), $oldDirectory);

        $this->assertSame('photos/portfolio/weddings', $newDirectory);
        $this->assertDirectoryDoesNotExist(Storage::disk('public')->path('photos/weddings'));
        $this->assertFileExists(Storage::disk('public')->path('photos/portfolio/weddings/photo-01.jpg'));
        $this->assertDatabaseHas('photos', [
            'gallery_id' => $gallery->id,
            'path' => '/storage/photos/portfolio/weddings/photo-01.jpg',
        ]);
    }
}
