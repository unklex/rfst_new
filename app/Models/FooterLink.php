<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FooterLink extends Model
{
    use HasSortOrder;

    protected $fillable = ['footer_column_id', 'label', 'url', 'is_external', 'sort', 'is_active'];

    protected $casts = [
        'is_external' => 'boolean',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function column(): BelongsTo
    {
        return $this->belongsTo(FooterColumn::class, 'footer_column_id');
    }
}
