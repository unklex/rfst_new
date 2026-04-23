@php
    $f = app(\App\Settings\FooterSettings::class);
    $general = app(\App\Settings\GeneralSettings::class);
    $columns = \App\Models\FooterColumn::ordered()->active()->with(['links' => fn ($q) => $q->where('is_active', true)->orderBy('sort')])->get();

    $accentChar = $f->massive_italic_char;
    $wordmark = $f->massive_wordmark;
    $pos = mb_strpos($wordmark, $accentChar);
    if ($pos === false) {
        $massiveHtml = e($wordmark);
    } else {
        $massiveHtml = e(mb_substr($wordmark, 0, $pos)) . '<em>' . e($accentChar) . '</em>' . e(mb_substr($wordmark, $pos + mb_strlen($accentChar)));
    }
@endphp
<footer>
    <div class="frame">
        <div class="top">
            <div class="brand">
                <a href="#" class="brand" style="gap:12px">
                    <div class="mark" style="color:var(--paper);border-color:var(--paper)">{{ $general->brand_mark_letter }}</div>
                    <div class="wm" style="color:var(--paper)">{{ $general->brand_wordmark }}<small style="color:var(--ink-4)">{{ $general->brand_subtitle }}</small></div>
                </a>
                <p>{{ $f->about_paragraph }}</p>
            </div>
            @foreach ($columns as $col)
                <div>
                    <h6>{{ $col->heading }}</h6>
                    <ul>
                        @foreach ($col->links as $link)
                            <li><a href="{{ $link->url }}"@if ($link->is_external) target="_blank" rel="noopener"@endif>{{ $link->label }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="massive">{!! $massiveHtml !!}</div>

        <div class="bot">
            <div>{!! $f->copyright_html !!}</div>
            <div style="display:flex;gap:20px">
                <a href="{{ $f->legal_link_policy_url }}">{{ $f->legal_link_policy_label }}</a>
                <a href="{{ $f->legal_link_oferta_url }}">{{ $f->legal_link_oferta_label }}</a>
                <a href="{{ $f->legal_link_152fz_url }}">{{ $f->legal_link_152fz_label }}</a>
            </div>
        </div>
    </div>
</footer>
