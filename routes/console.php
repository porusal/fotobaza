<?php

use App\Support\FilesystemGallerySync;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('gallery:sync-filesystem', function () {
    $result = app(FilesystemGallerySync::class)->sync();

    if (! empty($result['error'])) {
        $this->error($result['error']);

        return 1;
    }

    $this->info('Filesystem gallery sync completed.');
    $this->line('Galleries created: ' . ($result['galleries_created'] ?? 0));
    $this->line('Galleries updated: ' . ($result['galleries_updated'] ?? 0));
    $this->line('Files scanned: ' . ($result['photos_scanned'] ?? 0));
    $this->line('Photos created: ' . ($result['photos_created'] ?? 0));
    $this->line('Photos updated: ' . ($result['photos_updated'] ?? 0));

    return 0;
})->purpose('Import gallery folders and photos from public storage');
