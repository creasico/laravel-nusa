<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Http\Resources\NusaResource;
use Creasi\Nusa\Models\District;

class DistrictController
{
    public function index()
    {
        $districts = District::query();

        return NusaResource::collection($districts->paginate());
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
