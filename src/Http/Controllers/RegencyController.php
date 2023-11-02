<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;

final class RegencyController
{
    public function __construct(
        private Regency $model
    ) {
        // .
    }

    public function index(NusaRequest $request)
    {
        $request->relations($this->model);

        return NusaResource::collection($request->apply($this->model));
    }

    public function show(NusaRequest $request, int $regency)
    {
        $regency = $this->model->findOrFail($regency);

        $request->relations($regency);

        return new NusaResource($regency);
    }

    public function districts(int $regency)
    {
        $regency = $this->model->findOrFail($regency);

        return NusaResource::collection($regency->districts()->paginate());
    }

    public function villages(int $regency)
    {
        $regency = $this->model->findOrFail($regency);

        return NusaResource::collection($regency->villages()->paginate());
    }
}
