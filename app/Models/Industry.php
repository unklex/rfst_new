<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    use HasSortOrder;

    protected $fillable = [
        'number', 'title_html', 'subtitle',
        'class_codes', 'class_label', 'class_caption',
        'sort', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];
}
