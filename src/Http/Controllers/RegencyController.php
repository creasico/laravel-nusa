<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Http\Resources\NusaResource;

class RegencyController
{
    public function index()
    {
        return NusaResource::collection([]);
    }

    public function show()
    {
        return new NusaResource([]);
    }

    public function districts(Regency $regency)
    {
        return NusaResource::collection([]);
    }

    public function villages(Regency $regency)
    {
        return NusaResource::collection([]);
    }
}
