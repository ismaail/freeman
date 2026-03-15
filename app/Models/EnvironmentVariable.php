<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvironmentVariable extends Model
{
    public $timestamps = false;

    protected $fillable = ['environment_id', 'key', 'value', 'enabled'];

    protected $casts = ['enabled' => 'boolean'];

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }
}
