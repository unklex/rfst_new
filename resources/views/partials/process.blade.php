@php
    $s = app(\App\Settings\ProcessSectionSettings::class);
    $steps = \App\Models\ProcessStep::ordered()->active()->get();
@endphp
<section class="sec process">
    <div class="frame">
        <x-section-head
            idx="{{ $s->section_index }}"
            kicker="{{ $s->section_kicker }}"
            :heading-html="$s->section_heading_html"
            :note-html="$s->section_note_html"
        />
    </div>

    <div class="frame">
        <div class="proc-grid">
            @foreach ($steps as $step)
                <div class="proc">
                    <div class="num"><em>{{ $step->number }}</em></div>
                    <h6>{{ $step->title }}</h6>
                    <p>{{ $step->description }}</p>
                    <div class="meta"><span>{{ $step->meta_label }}</span><b>{{ $step->meta_value }}</b></div>
                </div>
            @endforeach
        </div>
    </div>
</section>
