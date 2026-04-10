<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Request extends Model
{
    protected $fillable = [
        'collection_id',
        'folder_id',
        'user_id',
        'name',
        'method',
        'url',
        'headers',
        'body_type',
        'raw_body_type',
        'body',
        'auth_type',
        'auth_data',
    ];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'auth_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(CollectionFolder::class, 'folder_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RequestLog::class);
    }
}
