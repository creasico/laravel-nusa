<?php

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
];
