<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\CollectionFolder;
use App\Repositories\CollectionRepository;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CollectionService
{
    public function __construct(private CollectionRepository $repo) {}

    public function listForUser(int $userId): EloquentCollection
    {
        return $this->repo->allForUserWithTree($userId);
    }

    public function create(int $userId, array $data): Collection
    {
        return $this->repo->create($userId, $data);
    }

    public function update(int $id, int $userId, array $data): Collection
    {
        $collection = $this->repo->findForUser($id, $userId);

        return $this->repo->update($collection, $data);
    }

    public function delete(int $id, int $userId): void
    {
        $collection = $this->repo->findForUser($id, $userId);
        $this->repo->delete($collection);
    }

    public function createFolder(int $collectionId, int $userId, array $data): CollectionFolder
    {
        // Verify collection ownership before creating inside it.
        $this->repo->findForUser($collectionId, $userId);

        // If nesting under a parent, verify the parent belongs to the same collection.
        if (! empty($data['parent_folder_id'])) {
            $this->repo->findFolder((int) $data['parent_folder_id'], $collectionId);
        }

        return $this->repo->createFolder($collectionId, $data);
    }

    public function updateFolder(int $folderId, int $collectionId, int $userId, array $data): CollectionFolder
    {
        $this->repo->findForUser($collectionId, $userId);
        $folder = $this->repo->findFolder($folderId, $collectionId);

        return $this->repo->updateFolder($folder, $data);
    }

    public function deleteFolder(int $folderId, int $collectionId, int $userId): void
    {
        $this->repo->findForUser($collectionId, $userId);
        $folder = $this->repo->findFolder($folderId, $collectionId);
        $this->repo->deleteFolder($folder);
    }
}
