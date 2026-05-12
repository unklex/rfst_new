@php
    $general = app(\App\Settings\GeneralSettings::class);
    $nav = app(\App\Settings\NavSettings::class);
    $navItems = \App\Models\NavItem::ordered()->active()->get();
@endphp
{{-- Mobile drawer: CSS-only toggle via #nav-toggle checkbox. Hamburger button (label) lives in nav.blade.php. --}}
<input type="checkbox" id="nav-toggle" class="nav-toggle" aria-hidden="true">
<label for="nav-toggle" class="nav-scrim" aria-label="Закрыть меню"></label>
<aside class="mobile-drawer" id="mobile-drawer" role="dialog" aria-modal="true" aria-label="Главное меню">
    <div class="mobile-drawer-head">
        <a href="#" class="brand">
            <div class="mark">{{ $general->brand_mark_letter }}</div>
            <div class="wm">{{ $general->brand_wordmark }}<small>{{ $general->brand_subtitle }}</small></div>
        </a>
        <label for="nav-toggle" class="drawer-close" aria-label="Закрыть меню">
            <span></span><span></span>
        </label>
    </div>
    <ul class="mobile-drawer-nav">
        @foreach ($navItems as $item)
            <li><a href="{{ $item->anchor }}">{{ $item->label }}</a></li>
        @endforeach
    </ul>
    <div class="mobile-drawer-foot">
        <a href="tel:{{ preg_replace('/[^\d+]/', '', $nav->phone_number) }}" class="phone">
            <small>{{ $nav->phone_label }}</small>{{ $nav->phone_number }}
        </a>
        <a href="#contact" class="btn">{{ $nav->primary_cta_label }} <span class="arr">→</span></a>
    </div>
</aside>
