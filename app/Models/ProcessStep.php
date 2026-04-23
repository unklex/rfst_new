<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;

class ProcessStep extends Model
{
    use HasSortOrder;

    protected $fillable = ['number', 'title', 'description', 'meta_label', 'meta_value', 'sort', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];
}
