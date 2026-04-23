<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitrixMockCard extends Model
{
    use HasSortOrder;

    protected $fillable = ['column_id', 'accent', 'label', 'value_html', 'sort', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function column(): BelongsTo
    {
        return $this->belongsTo(BitrixMockColumn::class, 'column_id');
    }
}
