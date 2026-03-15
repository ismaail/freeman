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
        $saved = $this->service->find($request, auth()->id());

        return response()->json(['data' => $saved]);
    }

    public function store(StoreRequestRequest $request): JsonResponse
    {
        $saved = $this->service->create(auth()->id(), $request->validated());

        return response()->json(['data' => $saved], 201);
    }

    public function update(UpdateRequestRequest $request, int $savedRequest): JsonResponse
    {
        $updated = $this->service->update($savedRequest, auth()->id(), $request->validated());

        return response()->json(['data' => $updated]);
    }

    public function destroy(int $savedRequest): JsonResponse
    {
        $this->service->delete($savedRequest, auth()->id());

        return response()->json(['message' => 'Request deleted.']);
    }

    /**
     * All requests in a collection (scoped to auth user).
     */
    public function indexForCollection(int $collection): JsonResponse
    {
        $requests = $this->service->forCollection($collection, auth()->id());

        return response()->json(['data' => $requests]);
    }

    /**
     * All requests in a folder (scoped to auth user via collection ownership).
     */
    public function indexForFolder(int $collection, int $folder): JsonResponse
    {
        $requests = $this->service->forFolder($folder, $collection, auth()->id());

        return response()->json(['data' => $requests]);
    }
}
