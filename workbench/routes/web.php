<?php

declare(strict_types=1);

use Creasi\Nusa\Models\Province;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/blade/{code?}', function (?string $code = null) {
    $connection = config('creasi.nusa.connection');
    $address = [
        'province_code' => '33',
        'regency_code' => '33.75',
        'district_code' => '',
        'village_code' => '',
    ];

    if ($code !== null) {
        $segments = explode('.', $code);
        $address = array_merge($address, match (\count($segments)) {
            4 => [
                'province_code' => $segments[0],
                'regency_code' => implode('.', \array_slice($segments, 0, 2)),
                'district_code' => implode('.', \array_slice($segments, 0, 3)),
                'village_code' => $code,
            ],
            3 => [
                'province_code' => $segments[0],
                'regency_code' => implode('.', \array_slice($segments, 0, 2)),
                'district_code' => $code,
            ],
            2 => [
                'province_code' => $segments[0],
                'regency_code' => $code,
            ],
            1 => [
                'province_code' => $code,
                'regency_code' => '',
            ],
            default => []
        });
    }

    return view('nusa-preview', [
        'nusa_db' => config('creasi.nusa.connection'),
        // `getenv()` reads the process environment directly: `$_ENV` is empty under some
        // `variables_order` settings and `$_SERVER` is not forwarded to the `php -S` child.
        // A plain `str_replace` would also rewrite `/home/alice-old` or, with `HOME=/`,
        // every slash in the path.
        'database' => Str::replaceStart((string) getenv('HOME'), '~', config("database.connections.{$connection}.database")),
        // A branch without an imported DB has no tables; preview must not 500.
        'provinces' => rescue(fn () => Province::count(), null, false),
        'address' => $address,
        'province_code' => $address['province_code'],
        'regency_code' => $address['regency_code'],
        'district_code' => $address['district_code'],
        'village_code' => $address['village_code'],
    ]);
})->name('nusa.preview');
