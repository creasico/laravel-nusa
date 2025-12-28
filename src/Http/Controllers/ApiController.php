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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ApiController
{
    use GeometryHelpers;

    public function index(ApiRequest $request, Province $model): JsonResponse|StreamedResponse
    {
        $data = $model->all();

        return match ($request->getAcceptable()) {
            'application/json' => response()->json($data->toArray(), 200),
            'text/csv' => $this->toCsv($data),
            default => response()->json([
                'message' => 'Only "application/json" or "text/csv" content types supported',
            ], 406),
        };
    }

    public function province(ApiRequest $request, Province $model): JsonResponse|StreamedResponse
    {
        /** @var \Creasi\Nusa\Models\Province */
        $data = $model->with('regencies')->findOrFail($request->code());

        return match ($request->getAcceptable()) {
            'application/json' => response()->json($data->toArray(), 200),
            'application/geo+json' => $this->toGeoJson($data),
            'text/csv' => $this->toCsv($data),
            default => response()->json([
                'message' => 'Only "application/json", "application/geo+json" or "text/csv" content types supported',
            ], 406),
        };
    }

    public function regency(ApiRequest $request, Regency $model): JsonResponse|StreamedResponse
    {
        /** @var \Creasi\Nusa\Models\Regency */
        $data = $model->with('districts')->findOrFail($request->code());

        return match ($request->getAcceptable()) {
            'application/json' => response()->json($data->toArray(), 200),
            'application/geo+json' => $this->toGeoJson($data),
            'text/csv' => $this->toCsv($data),
            default => response()->json([
                'message' => 'Only "application/json", "application/geo+json" or "text/csv" content types supported',
            ], 406),
        };
    }

    public function district(ApiRequest $request, District $model): JsonResponse|StreamedResponse
    {
        /** @var \Creasi\Nusa\Models\District */
        $data = $model->with('villages')->findOrFail($request->code());

        return match ($request->getAcceptable()) {
            'application/json' => response()->json($data->toArray(), 200),
            'application/geo+json' => $this->toGeoJson($data),
            'text/csv' => $this->toCsv($data),
            default => response()->json([
                'message' => 'Only "application/json", "application/geo+json" or "text/csv" content types supported',
            ], 406),
        };
    }

    public function village(ApiRequest $request, Village $model): JsonResponse
    {
        $data = $model->findOrFail($request->code());

        return match ($request->getAcceptable()) {
            'application/json' => response()->json($data->toArray(), 200),
            'application/geo+json' => $this->toGeoJson($data),
            default => response()->json([
                'message' => 'Only "application/json" or "application/geo+json" content types supported',
            ], 406),
        };
    }

    private function toCsv(Collection|HasSubdivision $data): StreamedResponse
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

    private function toGeoJson(HasCoordinate|Model $data): JsonResponse
    {
        if ($data->coordinates === null) {
            $path = str_replace('.', '/', $data->code);
            $geojson = Http::get("https://nusa.creasi.dev/static/{$path}.geojson");

            [$content, $type, $status] = match ($geojson->status()) {
                200 => [
                    $geojson->json(),
                    'application/geo+json',
                    200,
                ],
                default => [
                    ['message' => "The geojson for {$data->code} could not be found."],
                    'application/json',
                    404,
                ],
            };

            return response()->json($content, $status, [
                'Content-Type' => "{$type}; charset=UTF-8",
            ]);
        }

        $structure = $this->formatGeoJson(
            (string) str($data::class)->classBasename()->lower(),
            $data->code,
            $data->name,
            $data->longitude,
            $data->latitude,
            $data->coordinates
        );

        return response()->json($structure, 200, [
            'Content-Type' => 'application/geo+json; charset=UTF-8',
        ]);
    }
}
