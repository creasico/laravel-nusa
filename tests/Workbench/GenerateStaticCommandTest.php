<?php

declare(strict_types=1);

use Creasi\Tests\TestCase;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

it('generates static json, csv and geojson files', function () {
    /** @var TestCase $this */
    $root = \dirname(__DIR__, 2);
    $temp = Str::lower(Str::random(8));
    $database = "{$root}/workbench/storage/static-{$temp}.sqlite";
    $target = "workbench/storage/static-{$temp}";
    $targetPath = "{$root}/{$target}";
    $previousDatabase = config('database.connections.nusa.database');
    $previousConcurrency = Concurrency::getDefaultInstance();

    try {
        touch($database);

        config(['database.connections.nusa.database' => $database]);
        DB::purge('nusa');

        $this->artisan('migrate:fresh', [
            '--database' => 'nusa',
            '--path' => "{$root}/database/migrations",
            '--realpath' => true,
        ])->assertExitCode(0);

        $coordinates = json_encode([[[95.3, 5.5], [95.4, 5.5], [95.4, 5.6], [95.3, 5.5]]]);

        DB::connection('nusa')->table('provinces')->insert([
            'code' => '11',
            'name' => 'Aceh',
            'latitude' => 5.5,
            'longitude' => 95.3,
            'coordinates' => $coordinates,
        ]);
        DB::connection('nusa')->table('regencies')->insert([
            'code' => '11.75',
            'province_code' => '11',
            'name' => 'Kota Langsa',
            'latitude' => 5.47,
            'longitude' => 97.96,
            'coordinates' => $coordinates,
        ]);
        DB::connection('nusa')->table('districts')->insert([
            'code' => '11.75.03',
            'regency_code' => '11.75',
            'province_code' => '11',
            'name' => 'Langsa Barat',
            'latitude' => 5.47,
            'longitude' => 97.96,
            'coordinates' => $coordinates,
        ]);
        DB::connection('nusa')->table('villages')->insert([
            'code' => '11.75.03.1006',
            'district_code' => '11.75.03',
            'regency_code' => '11.75',
            'province_code' => '11',
            'name' => 'Kampung Baru',
            'postal_code' => '24411',
            'latitude' => 5.47,
            'longitude' => 97.96,
            'coordinates' => $coordinates,
        ]);

        Concurrency::setDefaultInstance('sync');

        $this->artisan('nusa:generate-static', [
            'target' => $target,
            '--link' => 'https://example.test',
        ])->assertExitCode(0);

        foreach ([
            'index.json',
            'index.csv',
            '11.json',
            '11.csv',
            '11/index.json',
            '11.geojson',
            '11/75.json',
            '11/75.csv',
            '11/75/index.json',
            '11/75.geojson',
            '11/75/03.json',
            '11/75/03.csv',
            '11/75/03/index.json',
            '11/75/03.geojson',
            '11/75/03/1006.json',
            '11/75/03/1006.geojson',
        ] as $file) {
            expect(file_exists("{$targetPath}/{$file}"))->toBeTrue("Missing {$file}");
        }

        $index = json_decode(file_get_contents("{$targetPath}/index.json"), true);

        expect($index[0]['href'])->toBe('https://example.test/11.json');
    } finally {
        Concurrency::setDefaultInstance($previousConcurrency);
        config(['database.connections.nusa.database' => $previousDatabase]);
        DB::purge('nusa');
        File::deleteDirectory($targetPath);
        @unlink($database);
    }
});
