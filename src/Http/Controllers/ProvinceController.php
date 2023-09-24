<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;
use Creasi\Nusa\Models\Province;

class ProvinceController
{
    public function index(NusaRequest $request, Province $province)
    {
        return NusaResource::collection($request->apply($province));
    }

    public function show(int $province)
    {
        $province = Province::query()->find($province);

        return new NusaResource($province);
    }

    public function regencies(int $province)
    {
        $province = Province::query()->findOrFail($province);

        return NusaResource::collection($province->regencies()->paginate());
    }

    public function districts(int $province)
    {
        $province = Province::query()->findOrFail($province);

        return NusaResource::collection($province->districts()->paginate());
    }

    public function villages(int $province)
    {
        $province = Province::query()->findOrFail($province);

        return NusaResource::collection($province->villages()->paginate());
    }
}
