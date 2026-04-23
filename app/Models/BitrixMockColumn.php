<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BitrixMockColumn extends Model
{
    use HasSortOrder;

    protected $fillable = ['title', 'badge', 'sort', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function cards(): HasMany
    {
        return $this->hasMany(BitrixMockCard::class, 'column_id')->orderBy('sort');
    }
}
