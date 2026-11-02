<?php

declare(strict_types=1);

use Creasi\Nusa\Models\Province;
use Illuminate\Support\Facades\Route;

Route::get('/blade', function () {
    $connection = config('creasi.nusa.connection');

    return view('nusa-preview', [
        'database' => (string) config("database.connections.{$connection}.database"),
        // A branch without an imported DB has no tables; preview must not 500.
        'provinces' => rescue(fn () => Province::count(), null, false),
    ]);
})->name('nusa.preview');
