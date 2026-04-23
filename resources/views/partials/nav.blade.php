@php
    $general = app(\App\Settings\GeneralSettings::class);
    $nav = app(\App\Settings\NavSettings::class);
    $navItems = \App\Models\NavItem::ordered()->active()->get();
@endphp
<nav class="nav">
    <div class="frame"><div class="row">
        <a href="#" class="brand">
            <div class="mark">{{ $general->brand_mark_letter }}</div>
            <div class="wm">{{ $general->brand_wordmark }}<small>{{ $general->brand_subtitle }}</small></div>
        </a>
        <ul>
            @foreach ($navItems as $item)
                <li><a href="{{ $item->anchor }}"@if ($loop->first) class="active"@endif>{{ $item->label }}</a></li>
            @endforeach
        </ul>
        <div class="cta">
            <div class="phone"><small>{{ $nav->phone_label }}</small>{{ $nav->phone_number }}</div>
            <a href="#contact" class="btn">{{ $nav->primary_cta_label }} <span class="arr">→</span></a>
        </div>
    </div></div>
</nav>
