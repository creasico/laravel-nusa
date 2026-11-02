<?php

declare(strict_types=1);

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;
use Creasi\Nusa\Models\Regency as RegencyModel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class RegencyController
{
    public function __construct(
        private RegencyModel $model
    ) {
        // .
    }

    public function index(NusaRequest $request): AnonymousResourceCollection
    {
        return NusaResource::collection($request->apply($this->model));
    }

    public function show(NusaRequest $request, string $regency): NusaResource
    {
        $regency = RegencyModel::query()->findOrFail($regency);

        $request->relations($regency);

        return new NusaResource($regency);
    }

    public function districts(NusaRequest $request, string $regency): AnonymousResourceCollection
    {
        $regency = RegencyModel::query()->findOrFail($regency);

        return NusaResource::collection(
            $request->apply($regency->districts())
        );
    }

    public function villages(NusaRequest $request, string $regency): AnonymousResourceCollection
    {
        $regency = RegencyModel::query()->findOrFail($regency);

        return NusaResource::collection(
            $request->apply($regency->villages())
        );
    }
}
