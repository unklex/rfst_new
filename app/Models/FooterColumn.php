<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FooterColumn extends Model
{
    use HasSortOrder;

    protected $fillable = ['heading', 'sort', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function links(): HasMany
    {
        return $this->hasMany(FooterLink::class)->orderBy('sort');
    }
}
