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
            'site_tagline' => 'Современная фотогалерея с атмосферой',
            'site_logo' => '/seed/logo.svg',
            'hero_image' => '/seed/hero.svg',
            'home_photos_count' => 8,
            'gallery_grid_columns' => 3,
            'grid_gap' => 'md',
            'hero_badge' => 'Сейчас открыта запись',
            'intro_text' => 'Фотоистории с мягким светом, четкой композицией и легкой подачей для портфолио, брендов и личных проектов.',
            'translate_languages' => ['en', 'lv'],
            'theme_text_color' => '#1c1712',
            'theme_heading_color' => '#1c1712',
            'theme_muted_color' => '#6e655d',
            'theme_accent_color' => '#a15f2d',
            'theme_accent_secondary_color' => '#2d6f67',
            'theme_accent_soft_color' => '#cf7158',
            'theme_tag_color' => '#2d6f67',
            'font_body' => 'manrope',
            'font_heading' => 'cormorant',
            'font_menu' => 'manrope',
            'font_catalog' => 'manrope',
            'font_tag' => 'manrope',
        ];

        foreach ($settings as $key => $value) {
            Setting::put($key, $value);
        }
    }
}
