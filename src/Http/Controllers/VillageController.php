<?php

declare(strict_types=1);

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;
use Creasi\Nusa\Models\Village as VillageModel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class VillageController
{
    public function __construct(
        private VillageModel $model
    ) {
        // .
    }

    public function index(NusaRequest $request): AnonymousResourceCollection
    {
        return NusaResource::collection($request->apply($this->model));
    }

    public function show(NusaRequest $request, string $village): NusaResource
    {
        $village = VillageModel::query()->findOrFail($village);

        $request->relations($village);

        return new NusaResource($village);
    }
}
