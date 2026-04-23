@php
    $q = app(\App\Settings\QuoteSettings::class);
    $reviewer = \App\Models\SiteAsset::where('key', 'quote_reviewer')->first();
    $reviewerWebp = $reviewer?->getFirstMediaUrl('image', 'webp') ?: null;
    $reviewerOriginal = $reviewer?->getFirstMediaUrl('image') ?: null;
    $reviewerAlt = trim((string) ($reviewer?->alt ?? '')) !== ''
        ? $reviewer->alt
        : $q->reviewer_name;
@endphp
<section class="quote">
    <div class="frame">
        <div class="side">
            @if ($reviewerWebp)
                <picture>
                    <source type="image/webp" srcset="{{ $reviewerWebp }}">
                    <img src="{{ $reviewerOriginal ?: $reviewerWebp }}" alt="{{ $reviewerAlt }}" loading="lazy" decoding="async"
                         style="width:80px;height:80px;object-fit:cover;border-radius:50%;margin-bottom:16px;display:block;filter:grayscale(1) contrast(1.05)">
                </picture>
            @endif
            {{ $q->reviewer_ref }}<br>
            <b>{{ $q->reviewer_name }}</b>
            {!! nl2br(e($q->reviewer_role)) !!}
        </div>
        <blockquote>
            {!! $q->quote_html !!}
        </blockquote>
        <div class="side">
            <b>{{ $q->company_name }}</b>
            {!! nl2br(e($q->company_description)) !!}
        </div>
    </div>
</section>
