<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use App\Services\CollectionService;
use Illuminate\Http\JsonResponse;

class CollectionController extends Controller
{
    public function __construct(private CollectionService $service) {}

    public function index(): JsonResponse
    {
        $collections = $this->service->listForUser(auth()->id());

        return response()->json(['data' => $collections]);
    }

    public function store(StoreCollectionRequest $request): JsonResponse
    {
        $collection = $this->service->create(auth()->id(), $request->validated());

        return response()->json(['data' => $collection, 'message' => 'Collection created.'], 201);
    }

    public function update(UpdateCollectionRequest $request, int $collection): JsonResponse
    {
        $updated = $this->service->update($collection, auth()->id(), $request->validated());

        return response()->json(['data' => $updated]);
    }

    public function destroy(int $collection): JsonResponse
    {
        $this->service->delete($collection, auth()->id());

        return response()->json(['message' => 'Collection deleted.']);
    }
}
