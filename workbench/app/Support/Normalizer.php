<?php

declare(strict_types=1);

namespace Workbench\App\Support;

class Normalizer
{
    public readonly ?string $type;

    public readonly ?array $coordinates;

    public static array $invalid = [];

    public static bool $dist = false;

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
                'name' => (string) str($this->name)->title(),
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'coordinates' => $this->normalizeCoordinates($this->coordinates),
            ],
            default => null
        };
    }

    private function toRegency(): array
    {
        [$province_code] = explode('.', $this->code, 2);

        return [
            'code' => $this->code,
            'province_code' => $province_code,
            'name' => (string) str($this->name)->title(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'coordinates' => $this->normalizeCoordinates($this->coordinates),
        ];
    }

    private function toDistrict(): array
    {
        [$province_code, $regency_code] = explode('.', $this->code, 3);

        return [
            'code' => $this->code,
            'regency_code' => $this->join($province_code, $regency_code),
            'province_code' => $province_code,
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'coordinates' => $this->normalizeCoordinates($this->coordinates),
        ];
    }

    private function toVillage(): array
    {
        [$province_code, $regency_code, $district_code] = explode('.', $this->code, 4);

        return [
            'code' => $this->code,
            'district_code' => $this->join($province_code, $regency_code, $district_code),
            'regency_code' => $this->join($province_code, $regency_code),
            'province_code' => $province_code,
            'name' => $this->name,
            'postal_code' => $this->postal_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'coordinates' => $this->normalizeCoordinates($this->coordinates),
        ];
    }

    private function join(string ...$codes): string
    {
        return implode('.', $codes);
    }

    private function normalizeCoordinates(?array $arr)
    {
        if (! $arr) {
            return null;
        }

        return json_encode($this->swapCoordinate($arr));
    }

    /**
     * Normalize the coordinates data from upstream.
     *
     * The upstream coordinates are formated in `[lat, lng]` format,
     * all we need is to flip them become `[lng, lat]` so it could be
     * viewed correctly natively in github `.geojson` preview.
     */
    private function swapCoordinate(array $arr): array
    {
        foreach ($arr as $key => $val) {
            if (
                count($val) === 2 &&
                (is_numeric($val[0]) && is_numeric($val[1]))
            ) {
                $arr[$key] = [$val[1], $val[0]];

                continue;
            }

            $arr[$key] = $this->swapCoordinate($val);
        }

        return $arr;
    }
}
