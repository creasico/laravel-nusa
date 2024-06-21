<?php

use Creasi\Nusa\Models\Address;

return [
    /**
     * Single Source Data Connection.
     */
    'connection' => env('CREASI_NUSA_CONNECTION', 'nusa'),

    'table_names' => [
        'provinces' => 'provinces',
        'districts' => 'districts',
        'regencies' => 'regencies',
        'villages' => 'villages',
    ],

    'addressable' => Address::class,

    'routes_enable' => env('CREASI_NUSA_ROUTES_ENABLE', true),

    'routes_prefix' => env('CREASI_NUSA_ROUTES_PREFIX', 'nusa'),
];
