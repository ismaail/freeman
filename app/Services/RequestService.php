<?php

namespace App\Services;

use App\Models\Request as ApiRequest;
use App\Repositories\CollectionRepository;
use App\Repositories\RequestRepository;
use Illuminate\Database\Eloquent\Collection;

class RequestService
{
    public function __construct(
        private RequestRepository $repo,
        private CollectionRepository $collections,
    ) {}

    public function find(int $id): ApiRequest
    {
        return $this->repo->find($id);
    }

    /**
     * All requests in a collection.
     */
    public function forCollection(int $collectionId): Collection
    {
        $this->collections->find($collectionId);

        return $this->repo->forCollection($collectionId);
    }

    /**
     * All requests in a folder — verifies folder belongs to the collection.
     */
    public function forFolder(int $folderId, int $collectionId): Collection
    {
        $this->collections->findFolder($folderId, $collectionId);

        return $this->repo->forFolder($folderId);
    }

    public function create(int $userId, array $data): ApiRequest
    {
        $this->authorizeCollectionAndFolder($data);

        return $this->repo->create($userId, $data);
    }

    public function update(int $id, array $data): ApiRequest
    {
        $request = $this->repo->find($id);

        $this->authorizeCollectionAndFolder($data);

        return $this->repo->update($request, $data);
    }

    public function delete(int $id): void
    {
        $request = $this->repo->find($id);
        $this->repo->delete($request);
    }

    public function duplicate(int $id, int $userId): ApiRequest
    {
        $request = $this->repo->find($id);
        $name    = 'Copy of '.$request->name;

        return $this->repo->duplicate($request, $userId, $name);
    }

    /**
     * When a collection_id or folder_id is supplied, verify they exist
     * and that the folder belongs to the collection.
     */
    private function authorizeCollectionAndFolder(array $data): void
    {
        if (! empty($data['collection_id'])) {
            $this->collections->find((int) $data['collection_id']);
        }

        if (! empty($data['folder_id'])) {
            $collectionId = (int) ($data['collection_id'] ?? 0);
            $this->collections->findFolder((int) $data['folder_id'], $collectionId);
        }
    }
}
