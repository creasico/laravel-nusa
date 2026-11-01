<?php

declare(strict_types=1);

use Creasi\Tests\Fixture\SearchableModel;
use Creasi\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

dataset('drivers', [
    'sqlite' => ['testing'],
    'mysql' => ['mysql'],
    'pgsql' => ['pgsql'],
]);

it('searches case-insensitively per driver', function (string $driver) {
    /** @var TestCase $this */
    if (! test_can_connect($driver)) {
        $this->markTestSkipped("The {$driver} connection is not available.");
    }

    Schema::connection($driver)->dropIfExists('searchables');
    Schema::connection($driver)->create('searchables', function (Blueprint $table) {
        $table->string('code')->primary();
        $table->string('name');
    });
    DB::connection($driver)->table('searchables')->insert([
        ['code' => '33', 'name' => 'Jawa Tengah'],
        ['code' => '32', 'name' => 'Jawa Barat'],
        ['code' => '11', 'name' => 'Aceh'],
    ]);

    SearchableModel::$connectionName = $driver;

    try {
        expect(SearchableModel::search('jawa')->count())->toBe(2)
            ->and(SearchableModel::search('JAWA')->count())->toBe(2)
            ->and(SearchableModel::search('sumatra')->count())->toBe(0);
    } finally {
        SearchableModel::$connectionName = null;
        Schema::connection($driver)->dropIfExists('searchables');
    }
})->with('drivers');
