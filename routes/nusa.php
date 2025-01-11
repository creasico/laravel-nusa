<?php

use Creasi\Nusa\Http\Controllers\DistrictController;
use Creasi\Nusa\Http\Controllers\ProvinceController;
use Creasi\Nusa\Http\Controllers\RegencyController;
use Creasi\Nusa\Http\Controllers\VillageController;
use Illuminate\Support\Facades\Route;

Route::controller(ProvinceController::class)->prefix('provinces')->group(function () {
    Route::get('', 'index')->name('provinces.index');
    Route::get('{province}', 'show')->name('provinces.show');
    Route::get('{province}/regencies', 'regencies')->name('provinces.regencies');
    Route::get('{province}/districts', 'districts')->name('provinces.districts');
    Route::get('{province}/villages', 'villages')->name('provinces.villages');
});

Route::controller(RegencyController::class)->prefix('regencies')->group(function () {
    Route::get('', 'index')->name('regencies.index');
    Route::get('{regency}', 'show')->name('regencies.show');
    Route::get('{regency}/districts', 'districts')->name('regencies.districts');
    Route::get('{regency}/villages', 'villages')->name('regencies.villages');
});

Route::controller(DistrictController::class)->prefix('districts')->group(function () {
    Route::get('', 'index')->name('districts.index');
    Route::get('{district}', 'show')->name('districts.show');
    Route::get('{district}/villages', 'villages')->name('districts.villages');
});

Route::controller(VillageController::class)->prefix('villages')->group(function () {
    Route::get('', 'index')->name('villages.index');
    Route::get('{village}', 'show')->name('villages.show');
});
