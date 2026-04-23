@php
    $c = app(\App\Settings\CtaBandSettings::class);
@endphp
<section class="cta-band">
    <div class="frame">
        <h3>{!! $c->heading_html !!}</h3>
        <div class="r">
            <p>{{ $c->paragraph }}</p>
            <a href="{{ $c->cta_primary_url }}" class="btn">{{ $c->cta_primary_label }} <span class="arr">→</span></a>
            <a href="{{ $c->cta_secondary_url }}" class="btn btn-ghost">{{ $c->cta_secondary_label }} <span class="arr">↓</span></a>
        </div>
    </div>
</section>
