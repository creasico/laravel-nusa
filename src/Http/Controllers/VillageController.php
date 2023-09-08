<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Http\Resources\NusaResource;
use Creasi\Nusa\Models\Village;

class VillageController
{
    public function index()
    {
        $villages = Village::query();

        return NusaResource::collection($villages->paginate());
    }

    public function show(int $village)
    {
        $village = Village::query()->find($village);

        return new NusaResource($village);
    }
}
