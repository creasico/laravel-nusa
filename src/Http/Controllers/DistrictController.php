<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;
use Creasi\Nusa\Models\District;

class DistrictController
{
    public function index(NusaRequest $request, District $district)
    {
        return NusaResource::collection($request->apply($district));
    }

    public function show(int $district)
    {
        $district = District::query()->find($district);

        return new NusaResource($district);
    }

    public function villages(int $district)
    {
        $district = District::query()->find($district);

        return NusaResource::collection($district->villages()->paginate());
    }
}
