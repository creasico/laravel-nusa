<?php

declare(strict_types=1);

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\HasCoordinate;
use Creasi\Nusa\Contracts\HasSubdivision;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Http\Requests\ApiRequest;
use Creasi\Nusa\Support\GeometryHelpers;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

final class ApiController
{
    use GeometryHelpers;

    public function index(ApiRequest $request, Province $model)
    {
        $data = $model->all();

        return match ($request->getAcceptable()) {
            'application/json' => $data,
            'text/csv' => $this->toCsv($data),
            default => response([
                'message' => 'Only "application/json" or "text/csv" content types supported',
            ], 406),
        };
    }

    public function province(ApiRequest $request, Province $model, string $province)
    {
        /** @var \Creasi\Nusa\Models\Province */
        $data = $model->with('regencies')->findOrFail($province);

        return match ($request->getAcceptable()) {
            'application/json' => $data,
            'application/geo+json' => $this->toGeoJson($data),
            'text/csv' => $this->toCsv($data),
            default => response([
                'message' => 'Only "application/json", "application/geo+json" or "text/csv" content types supported',
            ], 406),
        };
    }

    public function regency(ApiRequest $request, Regency $model, string $province, string $regency)
    {
        /** @var \Creasi\Nusa\Models\Regency */
        $data = $model->with('districts')->findOrFail("{$province}.{$regency}");

        return match ($request->getAcceptable()) {
            'application/json' => $data,
            'application/geo+json' => $this->toGeoJson($data),
            'text/csv' => $this->toCsv($data),
            default => response([
                'message' => 'Only "application/json", "application/geo+json" or "text/csv" content types supported',
            ], 406),
        };
    }

    public function district(ApiRequest $request, District $model, string $province, string $regency, string $district)
    {
        /** @var \Creasi\Nusa\Models\District */
        $data = $model->with('villages')->findOrFail("{$province}.{$regency}.{$district}");

        return match ($request->getAcceptable()) {
            'application/json' => $data,
            'application/geo+json' => $this->toGeoJson($data),
            'text/csv' => $this->toCsv($data),
            default => response([
                'message' => 'Only "application/json", "application/geo+json" or "text/csv" content types supported',
            ], 406),
        };
    }

    public function village(ApiRequest $request, Village $model, string $province, string $regency, string $district, string $village)
    {
        $data = $model->findOrFail("{$province}.{$regency}.{$district}.{$village}");

        return match ($request->getAcceptable()) {
            'application/json' => $data,
            'application/geo+json' => $this->toGeoJson($data),
            default => response([
                'message' => 'Only "application/json" or "application/geo+json" content types supported',
            ], 406),
        };
    }

    private function toCsv(Collection|HasSubdivision $data)
    {
        $list = $data instanceof Collection ? $data : $data->subdivisions();

        $headers = ['code', 'name', 'latitude', 'longitude'];

        $callback = function () use ($list, $headers) {
            $FH = fopen('php://output', 'w');
            fputcsv($FH, $headers);
            foreach ($list as $row) {
                fputcsv($FH, [
                    $row->code,
                    $row->name,
                    $row->latitude,
                    $row->longitude,
                ]);
            }
            fclose($FH);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function toGeoJson(HasCoordinate|Model $data)
    {
        $properties = [
            'code' => $data->code,
            'kind' => str($data::class)->classBasename()->lower(),
            'name' => $data->name,
        ];

        $structure = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => $properties,
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$data->longitude, $data->latitude],
                    ],
                ],
                [
                    'type' => 'Feature',
                    'properties' => $properties,
                    'geometry' => [
                        'type' => $this->getGeometryType($data->coordinates),
                        'coordinates' => $data->coordinates,
                    ],
                ],
            ],
        ];

        return response()->json($structure, 200, [
            'Content-Type' => 'application/geo+json; charset=UTF-8',
        ]);
    }
}
