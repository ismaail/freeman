<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestRequest;
use App\Http\Requests\UpdateRequestRequest;
use App\Services\RequestService;
use Illuminate\Http\JsonResponse;

class SavedRequestController extends Controller
{
    public function __construct(private RequestService $service) {}

    public function show(int $request): JsonResponse
    {
        $saved = $this->service->find($request);

        return response()->json(['data' => $saved]);
    }

    public function store(StoreRequestRequest $request): JsonResponse
    {
        $saved = $this->service->create(auth()->id(), $request->validated());

        return response()->json(['data' => $saved], 201);
    }

    public function update(UpdateRequestRequest $request, int $savedRequest): JsonResponse
    {
        $updated = $this->service->update($savedRequest, $request->validated());

        return response()->json(['data' => $updated]);
    }

    public function destroy(int $savedRequest): JsonResponse
    {
        $this->service->delete($savedRequest);

        return response()->json(['message' => 'Request deleted.']);
    }

    public function duplicate(int $savedRequest): JsonResponse
    {
        $copy = $this->service->duplicate($savedRequest, auth()->id());

        return response()->json(['data' => $copy], 201);
    }

    /**
     * All requests in a collection.
     */
    public function indexForCollection(int $collection): JsonResponse
    {
        $requests = $this->service->forCollection($collection);

        return response()->json(['data' => $requests]);
    }

    /**
     * All requests in a folder.
     */
    public function indexForFolder(int $collection, int $folder): JsonResponse
    {
        $requests = $this->service->forFolder($folder, $collection);

        return response()->json(['data' => $requests]);
    }
}
