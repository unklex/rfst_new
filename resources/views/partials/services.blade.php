@php
    $s = app(\App\Settings\ServicesSectionSettings::class);
    $services = \App\Models\Service::ordered()->active()->get();
@endphp
<section class="sec services" id="services">
    <div class="frame">
        <x-section-head
            idx="{{ $s->section_index }}"
            kicker="{{ $s->section_kicker }}"
            :heading-html="$s->section_heading_html"
            :note-html="$s->section_note_html"
        />
    </div>

    <div class="frame">
        <div class="svc-grid">
            @foreach ($services as $svc)
                <div class="svc @if ($svc->is_featured) featured @endif">
                    <div class="tag"><span>{{ $svc->line_label }}</span><b>{{ $svc->index_label }}</b></div>
                    <div class="sym"><em>{{ $svc->symbol }}</em></div>
                    <h4>{!! $svc->title_html !!}</h4>
                    <p>{{ $svc->description }}</p>
                    <div class="spec">
                        @foreach ($svc->spec_rows ?? [] as $row)
                            <div class="k">{{ $row['k'] }}</div><div class="v">{!! $row['v_html'] !!}</div>
                        @endforeach
                    </div>
                    <div class="foot"><span>→ читать далее</span><span class="go">{{ $svc->footer_code }}</span></div>
                </div>
            @endforeach
        </div>
    </div>
</section>
