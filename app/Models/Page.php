<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'pages';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'is_published',
        'show_in_menu',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_in_menu' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeVisibleInMenu(Builder $query): Builder
    {
        return $query->where('show_in_menu', true);
    }
}
