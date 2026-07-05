<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'Foto 636',
            'site_tagline' => 'Photography with atmosphere',
            'site_logo' => '/seed/logo.svg',
            'hero_image' => '/seed/hero.svg',
            'home_photos_count' => 8,
            'gallery_grid_columns' => 3,
            'grid_gap' => 'md',
            'hero_badge' => 'Open for commissions / 2026',
            'intro_text' => 'Фотоистории с мягким светом, чёткой композицией и лёгкой подачей для портфолио, брендов и личных проектов.',
        ];

        foreach ($settings as $key => $value) {
            Setting::put($key, $value);
        }
    }
}
