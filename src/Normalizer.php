<?php

declare(strict_types=1);

namespace Creasi\Nusa;

class Normalizer
{
    public readonly string $type;

    public function __construct(
        public readonly string $code,
        public readonly string $name,
    ) {
        $this->type = match (strlen($this->code)) {
            2 => 'provinces',
            5 => 'regencies',
            8 => 'districts',
            default => 'villages',
        };
    }

    public function toRegency()
    {
        [$province_code, $code] = explode('.', $this->code, 2);

        return [
            'code' => (int) ($province_code.$code),
            'province_code' => (int) $province_code,
            'name' => $this->name,
        ];
    }

    public function toDistrict()
    {
        [$province_code, $regency_code, $code] = explode('.', $this->code, 3);

        return [
            'code' => (int) ($province_code.$regency_code.$code),
            'regency_code' => (int) ($province_code.$regency_code),
            'province_code' => (int) $province_code,
            'name' => $this->name,
        ];
    }

    public function toVillage()
    {
        [$province_code, $regency_code, $district_code, $code] = explode('.', $this->code, 4);

        return [
            'code' => (int) ($province_code.$regency_code.$district_code.$code),
            'district_code' => (int) ($province_code.$regency_code.$district_code),
            'regency_code' => (int) ($province_code.$regency_code),
            'province_code' => (int) $province_code,
            'name' => $this->name,
        ];
    }
}
