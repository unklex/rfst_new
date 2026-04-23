<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;

class MetricTile extends Model
{
    use HasSortOrder;

    protected $fillable = ['key_label', 'key_strong', 'value_html', 'caption_html', 'sort', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];
}
