<?php

return [
    /**
     * Single Source Data Connection.
     */
    'connection' => env('CREASI_NUSA_CONNECTION', 'nusa'),

    'table_names' => [
        'villages' => 'villages',
        'districts' => 'districts',
        'regencies' => 'regencies',
        'provinces' => 'provinces',
    ],
];
