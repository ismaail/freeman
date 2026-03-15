<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use App\Services\CollectionExportService;
use App\Services\CollectionImportService;
use App\Services\CollectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CollectionController extends Controller
{
    public function __construct(
        private CollectionService $service,
        private CollectionExportService $exportService,
        private CollectionImportService $importService,
    ) {}

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

    // -------------------------------------------------------------------------
    // Export
    // -------------------------------------------------------------------------

    /**
     * GET /collections/{id}/export
     * Returns a downloadable Postman v2.1 JSON file.
     */
    public function export(int $collection): StreamedResponse
    {
        $data     = $this->exportService->export($collection, auth()->id());
        $json     = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $filename = 'freeman-collection-' . $collection . '.json';

        return response()->streamDownload(
            fn () => print($json),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    // -------------------------------------------------------------------------
    // Import
    // -------------------------------------------------------------------------

    /**
     * POST /collections/import
     * Accepts a JSON file upload and imports the Postman collection.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:json,txt', 'max:2048'],
        ]);

        $contents = file_get_contents($request->file('file')->getRealPath());
        $data     = json_decode($contents, true);

        if (! is_array($data)) {
            return response()->json(['message' => 'Invalid JSON file.'], 422);
        }

        try {
            $collection = $this->importService->import($data, auth()->id());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Collection imported successfully.',
            'data'    => ['id' => $collection->id, 'name' => $collection->name],
        ], 201);
    }
}
