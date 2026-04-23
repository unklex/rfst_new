<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;

class BitrixFeature extends Model
{
    use HasSortOrder;

    protected $fillable = ['number', 'title_html', 'subtitle', 'url', 'sort', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];
}
