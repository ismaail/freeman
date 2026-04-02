<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Services\CollectionService;
use Illuminate\Http\JsonResponse;

class FolderController extends Controller
{
    public function __construct(private CollectionService $service) {}

    public function store(StoreFolderRequest $request, int $collection): JsonResponse
    {
        $folder = $this->service->createFolder($collection, $request->validated());

        return response()->json(['data' => $folder], 201);
    }

    public function update(UpdateFolderRequest $request, int $collection, int $folder): JsonResponse
    {
        $updated = $this->service->updateFolder($folder, $collection, $request->validated());

        return response()->json(['data' => $updated]);
    }

    public function destroy(int $collection, int $folder): JsonResponse
    {
        $this->service->deleteFolder($folder, $collection);

        return response()->json(['message' => 'Folder deleted.']);
    }
}
