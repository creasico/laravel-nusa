<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Create a temporary database and target directory for static generation.
 */
function staticFixture(): array
{
    $root = \dirname(__DIR__, 2);
    $temp = Str::lower(Str::random(8));
    $database = "{$root}/workbench/storage/static-{$temp}.sqlite";
    $target = "workbench/storage/static-{$temp}";
    $targetPath = "{$root}/{$target}";
    $previousDatabase = config('database.connections.nusa.database');
    $previousConcurrency = Concurrency::getDefaultInstance();

    touch($database);
    config(['database.connections.nusa.database' => $database]);
    DB::purge('nusa');

    Artisan::call('migrate:fresh', [
        '--database' => 'nusa',
        '--path' => "{$root}/database/migrations",
        '--realpath' => true,
    ]);

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

    return compact('root', 'database', 'target', 'targetPath', 'previousDatabase', 'previousConcurrency');
}

/**
 * Restore config and remove files created by staticFixture().
 */
function cleanupStaticFixture(array $fixture): void
{
    Concurrency::setDefaultInstance($fixture['previousConcurrency']);
    config(['database.connections.nusa.database' => $fixture['previousDatabase']]);
    DB::purge('nusa');
    File::deleteDirectory($fixture['targetPath']);
    @unlink($fixture['database']);
}

it('generates static json, csv and geojson files', function () {
    $fixture = staticFixture();

    try {
        $this->artisan('nusa:generate-static', [
            'target' => $fixture['target'],
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
            expect(file_exists("{$fixture['targetPath']}/{$file}"))->toBeTrue("Missing {$file}");
        }

        $index = json_decode(file_get_contents("{$fixture['targetPath']}/index.json"), true);
        $province = json_decode(file_get_contents("{$fixture['targetPath']}/11.json"), true);
        $regency = json_decode(file_get_contents("{$fixture['targetPath']}/11/75.json"), true);
        $csv = file_get_contents("{$fixture['targetPath']}/11.csv");

        expect($index[0]['href'])->toBe('https://example.test/11.json')
            ->and($index[0]['name'])->toBe('Aceh')
            ->and($province['regencies'][0]['name'])->toBe('Kota Langsa')
            ->and($regency['name'])->toBe('Kota Langsa')
            ->and($csv)->toStartWith('code,province_code,name,latitude,longitude');
    } finally {
        cleanupStaticFixture($fixture);
    }
});

it('omits links without the link option', function () {
    $fixture = staticFixture();

    try {
        $this->artisan('nusa:generate-static', ['target' => $fixture['target']])->assertExitCode(0);

        $index = json_decode(file_get_contents("{$fixture['targetPath']}/index.json"), true);

        expect($index[0])->not->toHaveKey('href');
    } finally {
        cleanupStaticFixture($fixture);
    }
});

it('skips geojson for regions without coordinates', function () {
    $fixture = staticFixture();

    try {
        DB::connection('nusa')->table('provinces')->insert([
            'code' => '12',
            'name' => 'North Sumatra',
            'latitude' => 2.0,
            'longitude' => 99.0,
            'coordinates' => null,
        ]);

        $this->artisan('nusa:generate-static', ['target' => $fixture['target']])->assertExitCode(0);

        expect(file_exists("{$fixture['targetPath']}/11.geojson"))->toBeTrue()
            ->and(file_exists("{$fixture['targetPath']}/12.geojson"))->toBeFalse()
            ->and(file_exists("{$fixture['targetPath']}/12.json"))->toBeTrue();
    } finally {
        cleanupStaticFixture($fixture);
    }
});

it('generates an empty index for an empty database', function () {
    $fixture = staticFixture();

    try {
        foreach (['villages', 'districts', 'regencies', 'provinces'] as $table) {
            DB::connection('nusa')->table($table)->delete();
        }

        $this->artisan('nusa:generate-static', ['target' => $fixture['target']])->assertExitCode(0);

        expect(json_decode(file_get_contents("{$fixture['targetPath']}/index.json"), true))->toBe([])
            ->and(file_exists("{$fixture['targetPath']}/11.json"))->toBeFalse();
    } finally {
        cleanupStaticFixture($fixture);
    }
});
