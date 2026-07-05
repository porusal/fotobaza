<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class PhotoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'gallery' => 'studio-portraits',
                'filename' => 'studio-portrait-01.svg',
                'path' => '/seed/photos/studio-portrait-01.svg',
                'alt_text' => 'Studio portrait with warm light',
                'sort_order' => 10,
                'tags' => ['portrait', 'studio', 'monochrome'],
            ],
            [
                'gallery' => 'studio-portraits',
                'filename' => 'studio-portrait-02.svg',
                'path' => '/seed/photos/studio-portrait-02.svg',
                'alt_text' => 'Studio portrait with soft shadows',
                'sort_order' => 20,
                'tags' => ['portrait', 'studio'],
            ],
            [
                'gallery' => 'outdoor-portraits',
                'filename' => 'outdoor-portrait-01.svg',
                'path' => '/seed/photos/outdoor-portrait-01.svg',
                'alt_text' => 'Outdoor portrait in golden light',
                'sort_order' => 10,
                'tags' => ['portrait', 'outdoor', 'travel'],
            ],
            [
                'gallery' => 'fashion-stories',
                'filename' => 'fashion-story-01.svg',
                'path' => '/seed/photos/fashion-story-01.svg',
                'alt_text' => 'Fashion editorial frame',
                'sort_order' => 10,
                'tags' => ['editorial', 'fashion'],
            ],
            [
                'gallery' => 'fashion-stories',
                'filename' => 'fashion-story-02.svg',
                'path' => '/seed/photos/fashion-story-02.svg',
                'alt_text' => 'Editorial scene with movement',
                'sort_order' => 20,
                'tags' => ['editorial', 'fashion', 'night'],
            ],
            [
                'gallery' => 'behind-scenes',
                'filename' => 'bts-01.svg',
                'path' => '/seed/photos/bts-01.svg',
                'alt_text' => 'Behind the scenes on set',
                'sort_order' => 10,
                'tags' => ['behind-the-scenes', 'editorial'],
            ],
            [
                'gallery' => 'weddings',
                'filename' => 'wedding-01.svg',
                'path' => '/seed/photos/wedding-01.svg',
                'alt_text' => 'Wedding details and ambient light',
                'sort_order' => 10,
                'tags' => ['wedding', 'events', 'family'],
            ],
            [
                'gallery' => 'live-events',
                'filename' => 'event-01.svg',
                'path' => '/seed/photos/event-01.svg',
                'alt_text' => 'Live event crowd and lights',
                'sort_order' => 10,
                'tags' => ['events', 'night'],
            ],
            [
                'gallery' => 'events',
                'filename' => 'events-cover.svg',
                'path' => '/seed/photos/events-cover.svg',
                'alt_text' => 'Event catalog cover',
                'sort_order' => 1,
                'tags' => ['events'],
            ],
        ];

        foreach ($items as $item) {
            $gallery = Gallery::query()->where('slug', $item['gallery'])->firstOrFail();
            $tagNames = $item['tags'] ?? [];
            unset($item['gallery'], $item['tags']);

            $photo = Photo::query()->updateOrCreate(
                ['path' => $item['path']],
                $item + ['gallery_id' => $gallery->id]
            );

            $tagIds = Tag::query()
                ->whereIn('name', $tagNames)
                ->pluck('id')
                ->all();

            $photo->tags()->sync($tagIds);
        }
    }
}
