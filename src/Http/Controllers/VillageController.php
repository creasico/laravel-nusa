<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;

final class VillageController
{
    public function __construct(
        private Village $model
    ) {
        // .
    }

    public function index(NusaRequest $request)
    {
        return NusaResource::collection($request->apply($this->model));
    }

    public function show(NusaRequest $request, int $village)
    {
        $village = $this->model->findOrFail($village);

        $village->load($request->relations($village));

        return new NusaResource($village);
    }
}
