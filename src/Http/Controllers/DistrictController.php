<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Http\Resources\NusaResource;

class DistrictController
{
    public function index()
    {
        return NusaResource::collection([]);
    }

    public function show()
    {
        return new NusaResource([]);
    }

    public function villages(District $district)
    {
        return NusaResource::collection([]);
    }
}
