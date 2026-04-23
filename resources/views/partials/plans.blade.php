@php
    $s = app(\App\Settings\PlansSectionSettings::class);
    $plans = \App\Models\Plan::ordered()->active()->get();
@endphp
<section class="sec plans">
    <div class="frame">
        <x-section-head
            idx="{{ $s->section_index }}"
            kicker="{{ $s->section_kicker }}"
            heading-html="{{ $s->section_heading_html }}"
            note-html="{{ $s->section_note_html }}"
        />
    </div>

    <div class="frame">
        <div class="plan-grid">
            @foreach ($plans as $plan)
                <div class="plan @if ($plan->is_highlighted) hl @endif">
                    <div class="hd"><div class="t">{!! $plan->title_html !!}</div><div class="badge">{{ $plan->badge }}</div></div>
                    <div class="px">{!! $plan->price_main !!}<sup>{{ $plan->price_suffix }}</sup><small>{{ $plan->price_caption }}</small></div>
                    <ul>
                        @foreach ($plan->features ?? [] as $f)
                            <li>{{ $f }}</li>
                        @endforeach
                    </ul>
                    <div class="bt"><a href="{{ $plan->cta_url }}" class="btn">{{ $plan->cta_label }} <span class="arr">→</span></a></div>
                </div>
            @endforeach
        </div>
    </div>
</section>
