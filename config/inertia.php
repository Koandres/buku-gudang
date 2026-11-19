<?php

return [
    'pages' => [
        'ensure_pages_exist' => false,
        'paths' => [
            // Folder komponen memakai huruf kapital; runner Linux bersifat case-sensitive.
            resource_path('js/Pages'),
        ],
        'extensions' => [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ],
    ],
];
