<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Http\Resources\NusaResource;

class ProvinceController
{
    public function index()
    {
        return NusaResource::collection([]);
    }

    public function show()
    {
        return new NusaResource([]);
    }

    public function regencies(Province $province)
    {
        return NusaResource::collection([]);
    }

    public function districts(Province $province)
    {
        return NusaResource::collection([]);
    }

    public function villages(Province $province)
    {
        return NusaResource::collection([]);
    }
}
