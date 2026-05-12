<?php

namespace App\Repositories;

use App\Models\Request;
use App\Models\RequestLog;
use Illuminate\Database\Eloquent\Collection;

class RequestRepository
{
    /**
     * Single request — throws ModelNotFoundException if not found.
     */
    public function find(int $id): Request
    {
        return Request::findOrFail($id);
    }

    /**
     * All requests in a collection.
     */
    public function forCollection(int $collectionId): Collection
    {
        return Request::where('collection_id', $collectionId)
            ->orderBy('name')
            ->get();
    }

    /**
     * All requests in a folder.
     */
    public function forFolder(int $folderId): Collection
    {
        return Request::where('folder_id', $folderId)
            ->orderBy('name')
            ->get();
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

    public function duplicate(Request $request, int $userId, string $name): Request
    {
        return Request::create([
            'user_id'       => $userId,
            'collection_id' => $request->collection_id,
            'folder_id'     => $request->folder_id,
            'name'          => $name,
            'method'        => $request->method,
            'url'           => $request->url,
            'headers'       => $request->headers,
            'body_type'     => $request->body_type,
            'body'          => $request->body,
            'body_form'     => $request->body_form,
            'auth_type'     => $request->auth_type,
            'auth_data'     => $request->auth_data,
        ]);
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
