<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $galleries = [
            [
                'name' => 'portraits',
                'display_name' => 'Portraits',
                'slug' => 'portraits',
                'description' => '<p>Портреты с мягким светом, спокойной позой и вниманием к характеру.</p>',
                'cover_image' => '/seed/galleries/portraits.svg',
                'sort_order' => 10,
                'children' => [
                    [
                        'name' => 'studio-portraits',
                        'display_name' => 'Studio Portraits',
                        'slug' => 'studio-portraits',
                        'description' => '<p>Чистая студийная подача с акцентом на форму, линию и взгляд.</p>',
                        'cover_image' => '/seed/galleries/studio-portraits.svg',
                        'sort_order' => 10,
                    ],
                    [
                        'name' => 'outdoor-portraits',
                        'display_name' => 'Outdoor Portraits',
                        'slug' => 'outdoor-portraits',
                        'description' => '<p>Естественный свет, пространство и кадры, собранные вокруг атмосферы места.</p>',
                        'cover_image' => '/seed/galleries/outdoor-portraits.svg',
                        'sort_order' => 20,
                    ],
                ],
            ],
            [
                'name' => 'editorial',
                'display_name' => 'Editorial',
                'slug' => 'editorial',
                'description' => '<p>Истории для журналов, fashion-съёмок и визуальных кампаний.</p>',
                'cover_image' => '/seed/galleries/editorial.svg',
                'sort_order' => 20,
                'children' => [
                    [
                        'name' => 'fashion-stories',
                        'display_name' => 'Fashion Stories',
                        'slug' => 'fashion-stories',
                        'description' => '<p>Съёмки с выразительным стилингом и чистым модным ритмом.</p>',
                        'cover_image' => '/seed/galleries/fashion-stories.svg',
                        'sort_order' => 10,
                    ],
                    [
                        'name' => 'behind-scenes',
                        'display_name' => 'Behind the Scenes',
                        'slug' => 'behind-scenes',
                        'description' => '<p>Закулисье, рабочие моменты и динамика процесса без постановочной тяжести.</p>',
                        'cover_image' => '/seed/galleries/behind-scenes.svg',
                        'sort_order' => 20,
                    ],
                ],
            ],
            [
                'name' => 'events',
                'display_name' => 'Events',
                'slug' => 'events',
                'description' => '<p>Съёмки мероприятий, где важно поймать детали, эмоции и энергию зала.</p>',
                'cover_image' => '/seed/galleries/events.svg',
                'sort_order' => 30,
                'children' => [
                    [
                        'name' => 'weddings',
                        'display_name' => 'Weddings',
                        'slug' => 'weddings',
                        'description' => '<p>Тёплые моменты, свет и история дня, собранная без лишней театральности.</p>',
                        'cover_image' => '/seed/galleries/weddings.svg',
                        'sort_order' => 10,
                    ],
                    [
                        'name' => 'live-events',
                        'display_name' => 'Live Events',
                        'slug' => 'live-events',
                        'description' => '<p>Концерты, выступления и быстрые съёмки в живой среде.</p>',
                        'cover_image' => '/seed/galleries/live-events.svg',
                        'sort_order' => 20,
                    ],
                ],
            ],
        ];

        foreach ($galleries as $galleryData) {
            $children = $galleryData['children'] ?? [];
            unset($galleryData['children']);

            $parent = Gallery::query()->updateOrCreate(
                ['slug' => $galleryData['slug']],
                $galleryData + ['parent_id' => null, 'is_active' => true]
            );

            foreach ($children as $childData) {
                Gallery::query()->updateOrCreate(
                    ['slug' => $childData['slug']],
                    $childData + ['parent_id' => $parent->id, 'is_active' => true]
                );
            }
        }
    }
}
