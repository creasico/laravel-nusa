<?php

declare(strict_types=1);

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;
use Creasi\Nusa\Models\Province as ProvinceModel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProvinceController
{
    public function __construct(
        private ProvinceModel $model
    ) {
        // .
    }

    public function index(NusaRequest $request): AnonymousResourceCollection
    {
        return NusaResource::collection($request->apply($this->model));
    }

    public function show(NusaRequest $request, string $province): NusaResource
    {
        $province = ProvinceModel::query()->findOrFail($province);

        $request->relations($province);

        return new NusaResource($province);
    }

    public function regencies(NusaRequest $request, string $province): AnonymousResourceCollection
    {
        $province = ProvinceModel::query()->findOrFail($province);

        return NusaResource::collection(
            $request->apply($province->regencies())
        );
    }

    public function districts(NusaRequest $request, string $province): AnonymousResourceCollection
    {
        $province = ProvinceModel::query()->findOrFail($province);

        return NusaResource::collection(
            $request->apply($province->districts())
        );
    }

    public function villages(NusaRequest $request, string $province): AnonymousResourceCollection
    {
        $province = ProvinceModel::query()->findOrFail($province);

        return NusaResource::collection(
            $request->apply($province->villages())
        );
    }
}
