<?php

namespace App\Repositories;

use App\Models\Request;
use App\Models\RequestLog;
use Illuminate\Database\Eloquent\Collection;

class RequestRepository
{
    /**
     * All requests owned by a user.
     */
    public function allForUser(int $userId): Collection
    {
        return Request::where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    /**
     * All requests in a collection, scoped to owner.
     */
    public function forCollection(int $collectionId, int $userId): Collection
    {
        return Request::where('collection_id', $collectionId)
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    /**
     * All requests in a folder, scoped to owner.
     */
    public function forFolder(int $folderId, int $userId): Collection
    {
        return Request::where('folder_id', $folderId)
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Single request scoped to owner — throws ModelNotFoundException if not found or not owned.
     */
    public function findForUser(int $id, int $userId): Request
    {
        return Request::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function create(int $userId, array $data): Request
    {
        return Request::create(array_merge($data, ['user_id' => $userId]));
    }

    public function update(Request $request, array $data): Request
    {
        $request->update($data);

        return $request->fresh();
    }

    public function delete(Request $request): void
    {
        $request->delete();
    }

    // --- Request log methods ---

    public function log(array $data): RequestLog
    {
        return RequestLog::create($data);
    }

    /**
     * Paginated history for a user, newest first, with the originating request loaded.
     */
    public function historyForUser(int $userId, int $perPage = 50): \Illuminate\Pagination\LengthAwarePaginator
    {
        return RequestLog::where('user_id', $userId)
            ->with('request')
            ->orderBy('executed_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Most recent log entry for a given request (used to restore last response on workspace load).
     */
    public function lastLogForRequest(int $requestId): ?RequestLog
    {
        return RequestLog::where('request_id', $requestId)
            ->orderBy('executed_at', 'desc')
            ->first();
    }
}
