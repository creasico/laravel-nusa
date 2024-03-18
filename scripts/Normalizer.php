<?php

declare(strict_types=1);

namespace Creasi\Scripts;

class Normalizer
{
    public readonly ?string $type;

    public readonly ?array $coordinates;

    public static array $invalid = [];

    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $postal_code,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        ?string $coordinates = null,
    ) {
        $this->type = match (strlen($code)) {
            2 => 'provinces',
            5 => 'regencies',
            8 => 'districts',
            13 => 'villages',
            default => null,
        };

        if ($this->type === null) {
            self::$invalid[] = [$code, $name];
        }

        $this->coordinates = $coordinates ? \json_decode($coordinates) : null;
    }

    public function normalize()
    {
        return match ($this->type) {
            'villages' => $this->toVillage(),
            'districts' => $this->toDistrict(),
            'regencies' => $this->toRegency(),
            'provinces' => [
                'code' => (int) $this->code,
                'name' => str($this->name)->title(),
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'coordinates' => $this->coordinates,
            ],
            default => null
        };
    }

    private function toRegency(): array
    {
        [$province_code, $code] = explode('.', $this->code, 2);

        return [
            'code' => (int) ($province_code.$code),
            'province_code' => (int) $province_code,
            'name' => str($this->name)->title(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            // 'coordinates' =>  $this->coordinates,
        ];
    }

    private function toDistrict(): array
    {
        [$province_code, $regency_code, $code] = explode('.', $this->code, 3);

        return [
            'code' => (int) ($province_code.$regency_code.$code),
            'regency_code' => (int) ($province_code.$regency_code),
            'province_code' => (int) $province_code,
            'name' => $this->name,
            // 'latitude' => $this->latitude,
            // 'longitude' => $this->longitude,
            // 'coordinates' => $this->coordinates,
        ];
    }

    private function toVillage(): array
    {
        [$province_code, $regency_code, $district_code, $code] = explode('.', $this->code, 4);

        return [
            'code' => (int) ($province_code.$regency_code.$district_code.$code),
            'district_code' => (int) ($province_code.$regency_code.$district_code),
            'regency_code' => (int) ($province_code.$regency_code),
            'province_code' => (int) $province_code,
            'name' => $this->name,
            'postal_code' => $this->postal_code,
            // 'latitude' => $this->latitude,
            // 'longitude' => $this->longitude,
            // 'coordinates' => $this->coordinates,
        ];
    }
}
