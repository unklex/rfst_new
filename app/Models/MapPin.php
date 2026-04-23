<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;

class MapPin extends Model
{
    use HasSortOrder;

    protected $fillable = ['city_name', 'coordinates', 'position_class', 'sort', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];
}
