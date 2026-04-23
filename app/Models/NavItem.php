<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;

class NavItem extends Model
{
    use HasSortOrder;

    protected $fillable = ['label', 'anchor', 'is_external', 'sort', 'is_active'];

    protected $casts = [
        'is_external' => 'boolean',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];
}
