<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SiteAsset extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * Load the parent model's attributes inside registerMediaConversions()
     * so the per-key match() below actually sees $this->key.
     */
    public bool $registerMediaConversionsUsingModelInstance = true;

    protected $fillable = ['key', 'title', 'alt'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(82)
            ->width(match ($this->key) {
                'hero_bg', 'og_image' => 1920,
                'about_archive' => 1280,
                'quote_reviewer' => 480,
                'favicon' => 180,
                default => 1280,
            })
            ->nonQueued();

        if ($this->key !== 'favicon') {
            $this->addMediaConversion('webp_mobile')
                ->format('webp')
                ->quality(78)
                ->width(720)
                ->nonQueued();
        }
    }
}
