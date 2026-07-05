<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::query()->updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'Обо мне',
                'content' => <<<'HTML'
<p>Foto 636 — это небольшой фотопроект о свете, спокойствии и внимании к деталям.</p>
<p>Я работаю с портретами, editorial-съёмками и мероприятиями, предпочитая чистую композицию, мягкую тональность и честную подачу кадра.</p>
<p>Если нужен визуальный язык без лишнего шума, этот формат как раз для этого.</p>
HTML,
                'image' => '/seed/pages/about.svg',
                'is_published' => true,
                'show_in_menu' => true,
            ]
        );

        Page::query()->updateOrCreate(
            ['slug' => 'pricing'],
            [
                'title' => 'Услуги и стоимость',
                'content' => <<<'HTML'
<p>Страница-заглушка для описания пакетов, сроков и формата съёмки.</p>
<p>В рабочей версии здесь можно будет разместить PDF, тарифы и FAQ.</p>
HTML,
                'image' => '/seed/pages/pricing.svg',
                'is_published' => true,
                'show_in_menu' => true,
            ]
        );
    }
}
