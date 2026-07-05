<?php

return [
    'filesystem' => [
        'enabled' => env('GALLERY_FILESYSTEM_SYNC', true),
        'auto_sync' => env('GALLERY_FILESYSTEM_AUTO_SYNC', true),
        'auto_sync_seconds' => (int) env('GALLERY_FILESYSTEM_AUTO_SYNC_SECONDS', 30),
        'photos_directory' => env('GALLERY_FILESYSTEM_PHOTOS_DIR', 'photos'),
        'image_extensions' => [
            'jpg',
            'jpeg',
            'png',
            'webp',
            'gif',
            'avif',
            'svg',
        ],
    ],
];
