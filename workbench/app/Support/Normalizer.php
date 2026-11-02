<?php

declare(strict_types=1);

namespace Workbench\App\Support;

class Normalizer
{
    public readonly ?string $type;

    /** @var null|array<int, mixed> */
    public readonly ?array $coordinates;

    /** @var array<int, array{0: string, 1: string}> */
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
        $this->type = match (\strlen($code)) {
            2 => 'provinces',
            5 => 'regencies',
            8 => 'districts',
            13 => 'villages',
            default => null,
        };

        if ($this->type === null) {
            self::$invalid[] = [$code, $name];
        }

        $decoded = $coordinates ? json_decode($coordinates, true) : null;
        $this->coordinates = \is_array($decoded) ? $decoded : null;
    }

    /**
     * @return null|array<string, mixed>
     */
    public function normalize(): ?array
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

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @param  null|array<int, mixed>  $arr
     */
    private function normalizeCoordinates(?array $arr): ?string
    {
        if (! $arr) {
            return null;
        }

        return json_encode($this->swapCoordinate($arr)) ?: null;
    }

    /**
     * Normalize the coordinates data from upstream.
     *
     * The upstream coordinates are formated in `[lat, lng]` format,
     * all we need is to flip them become `[lng, lat]` so it could be
     * viewed correctly natively in github `.geojson` preview.
     *
     * @param  array<int, mixed>  $arr
     * @return array<int, mixed>
     */
    private function swapCoordinate(array $arr): array
    {
        if (\count($arr) === 2 && is_numeric($arr[0]) && is_numeric($arr[1])) {
            return [$arr[1], $arr[0]];
        }

        foreach ($arr as $key => $val) {
            if (\is_array($val)) {
                $arr[$key] = $this->swapCoordinate($val);
            }
        }

        return $arr;
    }
}
