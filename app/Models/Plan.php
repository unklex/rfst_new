<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasSortOrder;

    protected $fillable = [
        'title_html', 'badge',
        'price_main', 'price_suffix', 'price_caption',
        'features', 'cta_label', 'cta_url',
        'is_highlighted', 'sort', 'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'is_highlighted' => 'boolean',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];
}
