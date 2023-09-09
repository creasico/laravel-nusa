<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;
use Creasi\Nusa\Models\Village;

class VillageController
{
    public function index(NusaRequest $request, Village $village)
    {
        $villages = $request->apply($village);

        return NusaResource::collection($villages->paginate());
    }

    public function show(int $village)
    {
        $village = Village::query()->find($village);

        return new NusaResource($village);
    }
}
