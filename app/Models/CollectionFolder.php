<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionFolder extends Model
{
    protected $fillable = [
        'collection_id',
        'parent_folder_id',
        'name',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CollectionFolder::class, 'parent_folder_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CollectionFolder::class, 'parent_folder_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'folder_id');
    }
}
