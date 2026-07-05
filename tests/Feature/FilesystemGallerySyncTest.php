<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\Photo;
use App\Support\FilesystemGallerySync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FilesystemGallerySyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_ftp_directories_as_galleries(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('photos/events/weddings/photo-01.jpg', 'image');
        Storage::disk('public')->put('photos/events/concerts/live-01.webp', 'image');

        $result = app(FilesystemGallerySync::class)->sync();

        $events = Gallery::query()->where('slug', 'events')->firstOrFail();
        $weddings = Gallery::query()->where('slug', 'events-weddings')->firstOrFail();
        $concerts = Gallery::query()->where('slug', 'events-concerts')->firstOrFail();

        $this->assertSame(3, $result['galleries_created']);
        $this->assertSame(2, $result['photos_created']);
        $this->assertNull($events->parent_id);
        $this->assertSame($events->id, $weddings->parent_id);
        $this->assertSame($events->id, $concerts->parent_id);

        $this->assertDatabaseHas('photos', [
            'gallery_id' => $weddings->id,
            'filename' => 'photo-01.jpg',
            'path' => '/storage/photos/events/weddings/photo-01.jpg',
        ]);

        $this->assertDatabaseHas('photos', [
            'gallery_id' => $concerts->id,
            'filename' => 'live-01.webp',
            'path' => '/storage/photos/events/concerts/live-01.webp',
        ]);
    }

    public function test_public_gallery_route_syncs_missing_slug_before_404(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('photos/new-album/photo-01.jpg', 'image');

        $response = $this->get('/gallery/new-album');

        $response->assertOk();
        $this->assertDatabaseHas('galleries', [
            'slug' => 'new-album',
            'name' => 'new-album',
        ]);
        $this->assertSame(1, Photo::query()->count());
    }
}
