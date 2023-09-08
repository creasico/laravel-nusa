<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Http\Resources\NusaResource;

class VillageController
{
    public function index()
    {
        return NusaResource::collection([]);
    }

    public function show()
    {
        return new NusaResource([]);
    }
}
