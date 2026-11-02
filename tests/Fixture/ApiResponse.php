<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixture;

final class ApiResponse
{
    public const ACCEPT_HEADERS = ['application/json', '*/*', '*'];

    public static function geoJsonFields(): array
    {
        return [
            'type',
            'features' => [
                '*' => [
                    'type',
                    'properties',
                    'geometry' => ['type', 'coordinates'],
                ],
            ],
        ];
    }

    public static function provinceFields(): array
    {
        return [
            'code',
            'name',
            'latitude',
            'longitude',
            // 'coordinates'
        ];
    }

    public static function regencyFields(): array
    {
        return [
            'code',
            'name',
            'province_code',
            'latitude',
            'longitude',
            // 'coordinates'
        ];
    }

    public static function districtFields(): array
    {
        return [
            'code',
            'name',
            'regency_code',
            'province_code',
            'latitude',
            'longitude',
        ];
    }

    public static function villageFields(): array
    {
        return [
            'code',
            'name',
            'district_code',
            'regency_code',
            'province_code',
            'postal_code',
            'latitude',
            'longitude',
        ];
    }
}
