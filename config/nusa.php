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
];
