<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function getConnection()
    {
        return $this->connection = \config('creasi.nusa.connection');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('creasi.nusa.table_names');

        if (Schema::hasTable($tableNames['provinces'])) {
            return;
        }

        Schema::create($tableNames['provinces'], function (Blueprint $table) {
            $table->char('code', 2)->primary();
            $table->string('name', 50)->index();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->longText('coordinates')->nullable();
        });

        Schema::create($tableNames['regencies'], function (Blueprint $table) use ($tableNames) {
            $table->char('code', 5)->primary();
            $table->char('province_code', 2);
            $table->string('name', 50)->index();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->longText('coordinates')->nullable();

            $table->foreign('province_code')->references('code')->on($tableNames['provinces']);
        });

        Schema::create($tableNames['districts'], function (Blueprint $table) use ($tableNames) {
            $table->char('code', 8)->primary();
            $table->char('regency_code', 5);
            $table->char('province_code', 2);
            $table->string('name', 50)->index();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->longText('coordinates')->nullable();

            $table->foreign('regency_code')->references('code')->on($tableNames['regencies']);
            $table->foreign('province_code')->references('code')->on($tableNames['provinces']);
        });

        Schema::create($tableNames['villages'], function (Blueprint $table) use ($tableNames) {
            $table->char('code', 13)->primary();
            $table->char('district_code', 8);
            $table->char('regency_code', 5);
            $table->char('province_code', 2);
            $table->string('name', 50)->index();
            $table->char('postal_code', 5)->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->longText('coordinates')->nullable();

            $table->foreign('district_code')->references('code')->on($tableNames['districts']);
            $table->foreign('regency_code')->references('code')->on($tableNames['regencies']);
            $table->foreign('province_code')->references('code')->on($tableNames['provinces']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('creasi.nusa.table_names');

        Schema::dropIfExists($tableNames['villages']);
        Schema::dropIfExists($tableNames['districts']);
        Schema::dropIfExists($tableNames['regencies']);
        Schema::dropIfExists($tableNames['provinces']);
    }
};
