<?php

declare(strict_types=1);

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

    /**
     * Requests per minute allowed per IP on the API routes.
     *
     * Set to `0` to disable throttling. Limiting uses the application's default
     * cache store, so the `array` store — which resets every request — effectively
     * disables it outside of a single process.
     */
    'rate_limit' => env('CREASI_NUSA_RATE_LIMIT', 60),
];
