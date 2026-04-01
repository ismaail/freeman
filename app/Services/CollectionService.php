<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\CollectionFolder;
use App\Repositories\CollectionRepository;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CollectionService
{
    public function __construct(private CollectionRepository $repo) {}

    public function listAll(): EloquentCollection
    {
        return $this->repo->allWithTree();
    }

    public function create(int $userId, array $data): Collection
    {
        return $this->repo->create($userId, $data);
    }

    public function update(int $id, array $data): Collection
    {
        $collection = $this->repo->find($id);

        return $this->repo->update($collection, $data);
    }

    public function delete(int $id): void
    {
        $collection = $this->repo->find($id);
        $this->repo->delete($collection);
    }

    public function createFolder(int $collectionId, array $data): CollectionFolder
    {
        $this->repo->find($collectionId);

        if (! empty($data['parent_folder_id'])) {
            $this->repo->findFolder((int) $data['parent_folder_id'], $collectionId);
        }

        return $this->repo->createFolder($collectionId, $data);
    }

    public function updateFolder(int $folderId, int $collectionId, array $data): CollectionFolder
    {
        $folder = $this->repo->findFolder($folderId, $collectionId);

        return $this->repo->updateFolder($folder, $data);
    }

    public function deleteFolder(int $folderId, int $collectionId): void
    {
        $folder = $this->repo->findFolder($folderId, $collectionId);
        $this->repo->deleteFolder($folder);
    }

    // --- Variable methods ---

    public function getVariables(int $collectionId): EloquentCollection
    {
        $collection = $this->repo->find($collectionId);

        return $this->repo->getVariables($collection);
    }

    public function syncVariables(int $collectionId, array $variables): EloquentCollection
    {
        $collection = $this->repo->find($collectionId);

        return $this->repo->syncVariables($collection, $variables);
    }
}
