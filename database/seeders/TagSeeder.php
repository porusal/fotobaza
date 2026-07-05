<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'portrait',
            'studio',
            'editorial',
            'fashion',
            'events',
            'wedding',
            'travel',
            'outdoor',
            'monochrome',
            'night',
            'behind-the-scenes',
            'family',
        ];

        foreach ($tags as $tag) {
            Tag::query()->updateOrCreate(['name' => $tag]);
        }
    }
}
