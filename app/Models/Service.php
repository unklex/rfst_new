<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasSortOrder;

    protected $fillable = [
        'line_label', 'index_label', 'symbol', 'title_html', 'description',
        'spec_rows', 'footer_code', 'cta_url', 'is_featured', 'sort', 'is_active',
    ];

    protected $casts = [
        'spec_rows' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];
}
