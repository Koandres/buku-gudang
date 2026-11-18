<?php

return [
    'name' => env('APP_NAME', 'Buku Gudang'),
    'tagline' => 'Pengadaan & Gudang',

    'document_prefixes' => [
        'PR' => env('ERP_PREFIX_PR', 'PR'),
        'PO' => env('ERP_PREFIX_PO', 'PO'),
        'GR' => env('ERP_PREFIX_GR', 'GR'),
        'ADJ' => env('ERP_PREFIX_ADJ', 'ADJ'),
    ],

    // Pola nomor: PREFIX-YYYYMM-0001 (nomor diambil dari tabel document_sequences).
    'document_format' => '{prefix}-{period}-{sequence:04d}',

    'units' => ['pcs', 'box', 'kg', 'liter', 'set', 'unit', 'roll'],

    'currency' => [
        'code' => 'IDR',
        'symbol' => 'Rp',
        'decimals' => 0,
    ],
];
