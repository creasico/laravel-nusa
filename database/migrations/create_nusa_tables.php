<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('creasi.nusa.table_names');

        Schema::create($tableNames['provinces'], function (Blueprint $table) {
            $table->char('code', 2)->primary();
            $table->string('name');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->longText('coordinates')->nullable();
        });

        Schema::create($tableNames['regencies'], function (Blueprint $table) use ($tableNames) {
            $table->char('code', 4)->primary();
            $table->char('province_code', 2);
            $table->string('name');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->longText('coordinates')->nullable();

            $table->foreign('province_code')->references('code')->on($tableNames['provinces']);
        });

        Schema::create($tableNames['districts'], function (Blueprint $table) use ($tableNames) {
            $table->char('code', 6)->primary();
            $table->char('regency_code', 4);
            $table->char('province_code', 2);
            $table->string('name');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->longText('coordinates')->nullable();

            $table->foreign('regency_code')->references('code')->on($tableNames['regencies']);
            $table->foreign('province_code')->references('code')->on($tableNames['provinces']);
        });

        Schema::create($tableNames['villages'], function (Blueprint $table) use ($tableNames) {
            $table->char('code', 10)->primary();
            $table->char('district_code', 6);
            $table->char('regency_code', 4);
            $table->char('province_code', 2);
            $table->string('name');
            $table->char('postal_code', 5)->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->longText('coordinates')->nullable();

            $table->foreign('district_code')->references('code')->on($tableNames['districts']);
            $table->foreign('regency_code')->references('code')->on($tableNames['regencies']);
            $table->foreign('province_code')->references('code')->on($tableNames['provinces']);
        });

        Schema::create('addresses', function (Blueprint $table) use ($tableNames) {
            $table->id();
            $table->nullableMorphs('owner');
            $table->string('line');
            $table->char('village_code', 10)->nullable();
            $table->char('district_code', 6)->nullable();
            $table->char('regency_code', 4)->nullable();
            $table->char('province_code', 2)->nullable();
            $table->char('postal_code', 5)->nullable();

            $table->foreign('village_code')->references('code')->on($tableNames['villages'])->nullOnDelete();
            $table->foreign('district_code')->references('code')->on($tableNames['districts'])->nullOnDelete();
            $table->foreign('regency_code')->references('code')->on($tableNames['regencies'])->nullOnDelete();
            $table->foreign('province_code')->references('code')->on($tableNames['provinces'])->nullOnDelete();

            $table->timestamps();
        });

        Schema::create('has_addresses', function (Blueprint $table) {
            $table->id();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('creasi.nusa.table_names');

        Schema::dropIfExists('has_addresses');
        Schema::dropIfExists('address');
        Schema::dropIfExists($tableNames['villages']);
        Schema::dropIfExists($tableNames['districts']);
        Schema::dropIfExists($tableNames['regencies']);
        Schema::dropIfExists($tableNames['provinces']);
    }
};
