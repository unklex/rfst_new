@props(['wasteType'])
@php
    $imgUrl = $wasteType->getFirstMediaUrl('image', 'webp') ?: null;
@endphp
<div class="fig">
    @if ($imgUrl)
        <picture>
            <img src="{{ $imgUrl }}" alt="{{ strip_tags($wasteType->title_html) }}" loading="lazy">
        </picture>
    @else
        <div class="gly">{{ $wasteType->glyph }}</div>
    @endif
</div>
