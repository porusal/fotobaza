<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $table = 'tags';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(Photo::class)
            ->orderBy('photos.sort_order')
            ->orderByDesc('photos.created_at');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }
}
