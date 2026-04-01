<?php

namespace App\Repositories;

use App\Models\Collection;
use App\Models\CollectionFolder;
use App\Models\CollectionVariable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CollectionRepository
{
    /**
     * All collections with their full tree, ordered by name.
     */
    public function allWithTree(): EloquentCollection
    {
        return Collection::with([
            'folders' => fn ($q) => $q->orderBy('name'),
            'folders.requests' => fn ($q) => $q->orderBy('name'),
            'requests' => fn ($q) => $q->whereNull('folder_id')->orderBy('name'),
        ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Single collection — throws ModelNotFoundException if not found.
     */
    public function find(int $id): Collection
    {
        return Collection::findOrFail($id);
    }

    /**
     * Collection with its full folder/request tree loaded.
     */
    public function withTree(int $id): Collection
    {
        return Collection::where('id', $id)
            ->with([
                'folders' => fn ($q) => $q->orderBy('name'),
                'folders.children' => fn ($q) => $q->orderBy('name'),
                'folders.requests' => fn ($q) => $q->orderBy('name'),
                'folders.children.requests' => fn ($q) => $q->orderBy('name'),
                'requests' => fn ($q) => $q->whereNull('folder_id')->orderBy('name'),
            ])
            ->firstOrFail();
    }

    public function create(int $userId, array $data): Collection
    {
        return Collection::create([
            'user_id' => $userId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function update(Collection $collection, array $data): Collection
    {
        $collection->update($data);

        return $collection->fresh();
    }

    public function delete(Collection $collection): void
    {
        $collection->delete();
    }

    // --- Folder methods ---

    public function createFolder(int $collectionId, array $data): CollectionFolder
    {
        return CollectionFolder::create([
            'collection_id' => $collectionId,
            'parent_folder_id' => $data['parent_folder_id'] ?? null,
            'name' => $data['name'],
        ]);
    }

    public function findFolder(int $folderId, int $collectionId): CollectionFolder
    {
        return CollectionFolder::where('id', $folderId)
            ->where('collection_id', $collectionId)
            ->firstOrFail();
    }

    public function updateFolder(CollectionFolder $folder, array $data): CollectionFolder
    {
        $folder->update($data);

        return $folder->fresh();
    }

    public function deleteFolder(CollectionFolder $folder): void
    {
        $folder->delete();
    }

    // --- Variable methods ---

    public function getVariables(Collection $collection): EloquentCollection
    {
        return $collection->variables()->get();
    }

    public function syncVariables(Collection $collection, array $variables): EloquentCollection
    {
        $collection->variables()->delete();

        foreach ($variables as $var) {
            if (isset($var['key']) && $var['key'] !== '') {
                CollectionVariable::create([
                    'collection_id' => $collection->id,
                    'key'           => $var['key'],
                    'value'         => $var['value'] ?? '',
                    'enabled'       => $var['enabled'] ?? true,
                ]);
            }
        }

        return $collection->variables()->get();
    }

    /**
     * Returns a flat key → value map of enabled variables for a collection.
     */
    public function getVariablesMap(int $collectionId): array
    {
        $vars = [];

        CollectionVariable::where('collection_id', $collectionId)
            ->where('enabled', true)
            ->get()
            ->each(function (CollectionVariable $v) use (&$vars): void {
                $vars[$v->key] = $v->value;
            });

        return $vars;
    }
}
