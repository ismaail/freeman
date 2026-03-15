<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnvironmentRequest;
use App\Http\Requests\UpdateEnvironmentRequest;
use App\Services\EnvironmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnvironmentController extends Controller
{
    public function __construct(private EnvironmentService $service) {}

    public function index(): JsonResponse
    {
        $environments = $this->service->listForUser(auth()->id());

        return response()->json($environments);
    }

    public function store(StoreEnvironmentRequest $request): JsonResponse
    {
        $data        = $request->validated();
        $environment = $this->service->create(auth()->id(), $data);

        if (! empty($data['variables'])) {
            $environment = $this->service->update($environment->id, auth()->id(), $data);
        }

        return response()->json($environment->load('variables'), 201);
    }

    public function update(UpdateEnvironmentRequest $request, int $id): JsonResponse
    {
        $environment = $this->service->update($id, auth()->id(), $request->validated());

        return response()->json($environment);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id, auth()->id());

        return response()->json(null, 204);
    }

    public function activate(int $id): JsonResponse
    {
        $environment = $this->service->activate($id, auth()->id());

        return response()->json($environment);
    }

    public function deactivate(): JsonResponse
    {
        $this->service->deactivate(auth()->id());

        return response()->json(['active' => null]);
    }
}
