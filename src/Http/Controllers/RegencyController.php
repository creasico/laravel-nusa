<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;
use Creasi\Nusa\Models\Regency;

class RegencyController
{
    public function index(NusaRequest $request, Regency $regency)
    {
        return NusaResource::collection($request->apply($regency));
    }

    public function show(int $regency)
    {
        $regency = Regency::query()->find($regency);

        return new NusaResource($regency);
    }

    public function districts(int $regency)
    {
        $regency = Regency::query()->find($regency);

        return NusaResource::collection($regency->districts()->paginate());
    }

    public function villages(int $regency)
    {
        $regency = Regency::query()->find($regency);

        return NusaResource::collection($regency->villages()->paginate());
    }
}
