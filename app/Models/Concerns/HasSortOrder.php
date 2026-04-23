<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasSortOrder
{
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
