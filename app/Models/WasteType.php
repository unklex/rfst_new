<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class WasteType extends Model implements HasMedia
{
    use HasSortOrder;
    use InteractsWithMedia;

    protected $fillable = [
        'fkko_code', 'glyph', 'title_html', 'description',
        'hazard_class_label', 'is_hazard',
        'sort', 'is_active',
    ];

    protected $casts = [
        'is_hazard' => 'boolean',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(82)
            ->width(640)
            ->nonQueued();

        $this->addMediaConversion('webp_thumb')
            ->format('webp')
            ->quality(78)
            ->width(320)
            ->nonQueued();
    }
}
