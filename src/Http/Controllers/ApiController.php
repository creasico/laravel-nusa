<?php

declare(strict_types=1);

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Contracts\HasSubdivision;
use Creasi\Nusa\Http\Requests\ApiRequest;
use Creasi\Nusa\Models\District as DistrictModel;
use Creasi\Nusa\Models\Model as NusaModel;
use Creasi\Nusa\Models\Province as ProvinceModel;
use Creasi\Nusa\Models\Regency as RegencyModel;
use Creasi\Nusa\Models\Village as VillageModel;
use Creasi\Nusa\Support\GeometryHelpers;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ApiController
{
    use GeometryHelpers;

    public function index(ApiRequest $request, ProvinceModel $model): JsonResponse|StreamedResponse
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

    public function province(ApiRequest $request, ProvinceModel $model): JsonResponse|StreamedResponse
    {
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

    public function regency(ApiRequest $request, RegencyModel $model): JsonResponse|StreamedResponse
    {
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

    public function district(ApiRequest $request, DistrictModel $model): JsonResponse|StreamedResponse
    {
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

    public function village(ApiRequest $request, VillageModel $model): JsonResponse
    {
        $data = $model->findOrFail($request->code());

        if (! $data instanceof NusaModel) {
            throw new \RuntimeException('Invalid village model.');
        }

        return match ($request->getAcceptable()) {
            'application/json' => response()->json($data->toArray(), 200),
            'application/geo+json' => $this->toGeoJson($data),
            default => response()->json([
                'message' => 'Only "application/json" or "application/geo+json" content types supported',
            ], 406),
        };
    }

    /**
     * @template TModel of NusaModel
     *
     * @param  Collection<int, TModel>|HasSubdivision<TModel>  $data
     */
    private function toCsv(Collection|HasSubdivision $data): StreamedResponse
    {
        $list = $data instanceof Collection ? $data : $data->subdivisions();

        $headers = ['code', 'name', 'latitude', 'longitude'];

        $callback = function () use ($list, $headers) {
            $FH = fopen('php://output', 'w');

            if ($FH === false) {
                return;
            }

            fputcsv($FH, $headers);
            foreach ($list as $row) {
                fputcsv($FH, [
                    (string) $row->getAttribute('code'),
                    (string) $row->getAttribute('name'),
                    (string) $row->getAttribute('latitude'),
                    (string) $row->getAttribute('longitude'),
                ]);
            }
            fclose($FH);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function toGeoJson(NusaModel $data): JsonResponse
    {
        if ($data->getAttribute('coordinates') === null) {
            $path = str_replace('.', '/', (string) $data->getAttribute('code'));
            $geojson = Http::acceptJson()->get("https://nusa.creasi.dev/api/{$path}.geojson");

            [$content, $type, $status] = match ($geojson->status()) {
                200 => [
                    $geojson->json(),
                    'application/geo+json',
                    200,
                ],
                default => [
                    ['message' => 'The geojson for '.$data->getAttribute('code').' could not be found.'],
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
            (string) $data->getAttribute('code'),
            (string) $data->getAttribute('name'),
            (float) $data->getAttribute('longitude'),
            (float) $data->getAttribute('latitude'),
            (array) $data->getAttribute('coordinates')
        );

        return response()->json($structure, 200, [
            'Content-Type' => 'application/geo+json; charset=UTF-8',
        ]);
    }
}
