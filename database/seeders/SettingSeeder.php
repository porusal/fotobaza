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
            'site_copyright' => '© 2026 Foto 636',
            'site_logo' => '/seed/logo.svg',
            'hero_image' => '/seed/hero.svg',
            'home_photos_count' => 8,
            'gallery_grid_columns_mobile' => 2,
            'gallery_grid_columns_tablet' => 3,
            'gallery_grid_columns' => 3,
            'grid_gap' => 'md',
            'hero_badge' => '',
            'intro_text' => 'Фотоистории с мягким светом, четкой композицией и легкой подачей для портфолио, брендов и личных проектов.',
            'translate_languages' => ['en', 'lv'],
            'theme_text_color' => '#1c1712',
            'theme_heading_color' => '#1c1712',
            'theme_muted_color' => '#6e655d',
            'theme_accent_color' => '#a15f2d',
            'theme_accent_secondary_color' => '#2d6f67',
            'theme_accent_soft_color' => '#cf7158',
            'theme_tag_color' => '#2d6f67',
            'theme_dark_text_color' => '#f4efe8',
            'theme_dark_heading_color' => '#fff6ea',
            'theme_dark_muted_color' => '#cfc4b9',
            'theme_dark_accent_color' => '#ffb871',
            'theme_dark_accent_secondary_color' => '#7bcfc1',
            'theme_dark_accent_soft_color' => '#f28a73',
            'theme_dark_tag_color' => '#7bcfc1',
            'font_body' => 'manrope',
            'font_heading' => 'cormorant',
            'font_menu' => 'manrope',
            'font_catalog' => 'manrope',
            'font_tag' => 'manrope',
            'font_body_style' => 'normal',
            'font_heading_style' => 'bold',
            'font_menu_style' => 'bold',
            'font_catalog_style' => 'bold',
            'font_tag_style' => 'bold',
            'font_body_size' => '12pt',
            'font_heading_size' => '42pt',
            'font_menu_size' => '11pt',
            'font_catalog_size' => '12pt',
            'font_tag_size' => '11pt',
        ];

        foreach ($settings as $key => $value) {
            Setting::put($key, $value);
        }
    }
}
