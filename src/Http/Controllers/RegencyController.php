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

    public function show(NusaRequest $request, int $regency)
    {
        $regency = Regency::query()->findOrFail($regency);

        $regency->load($request->relations($regency));

        return new NusaResource($regency);
    }

    public function districts(int $regency)
    {
        $regency = Regency::query()->findOrFail($regency);

        return NusaResource::collection($regency->districts()->paginate());
    }

    public function villages(int $regency)
    {
        $regency = Regency::query()->findOrFail($regency);

        return NusaResource::collection($regency->villages()->paginate());
    }
}
