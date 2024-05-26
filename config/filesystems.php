<?php

declare(strict_types=1);

return [
    'default' => 'local',
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => env('STORAGE_DIR', '.joblist/storage'),
            'throw' => false,
        ],
    ],
];
