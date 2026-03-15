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

    public function find(int $id, int $userId): ApiRequest
    {
        return $this->repo->findForUser($id, $userId);
    }

    /**
     * All requests in a collection — verifies collection ownership first.
     */
    public function forCollection(int $collectionId, int $userId): Collection
    {
        $this->collections->findForUser($collectionId, $userId);

        return $this->repo->forCollection($collectionId, $userId);
    }

    /**
     * All requests in a folder — verifies collection then folder ownership.
     */
    public function forFolder(int $folderId, int $collectionId, int $userId): Collection
    {
        $this->collections->findForUser($collectionId, $userId);
        $this->collections->findFolder($folderId, $collectionId);

        return $this->repo->forFolder($folderId, $userId);
    }

    public function create(int $userId, array $data): ApiRequest
    {
        $this->authorizeCollectionAndFolder($data, $userId);

        return $this->repo->create($userId, $data);
    }

    public function update(int $id, int $userId, array $data): ApiRequest
    {
        $request = $this->repo->findForUser($id, $userId);

        $this->authorizeCollectionAndFolder($data, $userId);

        return $this->repo->update($request, $data);
    }

    public function delete(int $id, int $userId): void
    {
        $request = $this->repo->findForUser($id, $userId);
        $this->repo->delete($request);
    }

    /**
     * When a collection_id or folder_id is supplied, verify the authenticated
     * user actually owns them before writing. Prevents cross-user data injection.
     */
    private function authorizeCollectionAndFolder(array $data, int $userId): void
    {
        if (! empty($data['collection_id'])) {
            $this->collections->findForUser((int) $data['collection_id'], $userId);
        }

        if (! empty($data['folder_id'])) {
            $collectionId = (int) ($data['collection_id'] ?? 0);
            $this->collections->findFolder((int) $data['folder_id'], $collectionId);
        }
    }
}
