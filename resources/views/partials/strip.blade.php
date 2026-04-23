@php
    $strip = app(\App\Settings\TopStripSettings::class);
@endphp
<div class="strip">
    <div class="frame"><div class="row">
        <div class="lhs">
            <span><span class="dot"></span>{{ $strip->status_text }}</span>
            <span>{{ $strip->location_text }}</span>
            <span>{{ $strip->license_text }}</span>
        </div>
        <div class="rhs">
            <span>{!! \Illuminate\Support\Str::of($strip->lang_label)->replace('en', '<a href="#">en</a>') !!}</span>
            @if ($strip->telegram_url)
                <a href="{{ $strip->telegram_url }}">telegram ↗</a>
            @endif
            @if ($strip->whatsapp_url)
                <a href="{{ $strip->whatsapp_url }}">whatsapp ↗</a>
            @endif
        </div>
    </div></div>
</div>
