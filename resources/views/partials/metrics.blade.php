@php
    $m = app(\App\Settings\MetricsSettings::class);
    $tiles = \App\Models\MetricTile::ordered()->active()->get();
@endphp
<section class="metrics">
    <div class="frame">
        <div class="head">
            <h3>{!! $m->header_html !!}</h3>
            <div class="stamp">{{ $m->stamp_text }}</div>
        </div>
        <div class="grid">
            @foreach ($tiles as $tile)
                <div class="metric">
                    <div class="k">{{ $tile->key_label }}<b>&nbsp;{{ $tile->key_strong }}</b></div>
                    <div class="v">{!! $tile->value_html !!}</div>
                    <div class="l">{!! $tile->caption_html !!}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
