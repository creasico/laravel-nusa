<?php

declare(strict_types=1);

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;
use Creasi\Nusa\Models\District as DistrictModel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class DistrictController
{
    public function __construct(
        private DistrictModel $model
    ) {
        // .
    }

    public function index(NusaRequest $request): AnonymousResourceCollection
    {
        return NusaResource::collection($request->apply($this->model));
    }

    public function show(NusaRequest $request, string $district): NusaResource
    {
        $district = DistrictModel::query()->findOrFail($district);

        $request->relations($district);

        return new NusaResource($district);
    }

    public function villages(NusaRequest $request, string $district): AnonymousResourceCollection
    {
        $district = DistrictModel::query()->findOrFail($district);

        return NusaResource::collection(
            $request->apply($district->villages())
        );
    }
}
