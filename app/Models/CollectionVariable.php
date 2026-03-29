<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionVariable extends Model
{
    public $timestamps = false;

    protected $fillable = ['collection_id', 'key', 'value', 'enabled'];

    protected $casts = ['enabled' => 'boolean'];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
