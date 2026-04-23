@php
    $c = app(\App\Settings\CoverageSettings::class);
    $regions = \App\Models\Region::ordered()->active()->get();
    $pins = \App\Models\MapPin::ordered()->active()->get();
@endphp
<section class="cov" id="cov">
    <div class="frame">
        <div class="cov-left">
            <div class="k">{{ $c->kicker }}</div>
            <h3>{!! $c->heading_html !!}</h3>
            <p>{{ $c->paragraph }}</p>

            <div class="regs">
                @foreach ($regions as $region)
                    <div class="r"><span>{{ $region->number }}</span><b>{{ $region->name }}</b></div>
                @endforeach
            </div>
        </div>
        <div class="cov-map">
            <div class="meta">{!! $c->map_meta_html !!}</div>
            @foreach ($pins as $pin)
                <div class="coord {{ $pin->position_class }}"><b>{{ $pin->city_name }}</b>{{ $pin->coordinates }}</div>
            @endforeach
        </div>
    </div>
</section>
