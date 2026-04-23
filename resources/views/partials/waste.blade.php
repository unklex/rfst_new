@php
    $s = app(\App\Settings\WasteSectionSettings::class);
    $cards = \App\Models\WasteType::ordered()->active()->get();
@endphp
<section class="sec waste">
    <div class="frame">
        <x-section-head
            idx="{{ $s->section_index }}"
            kicker="{{ $s->section_kicker }}"
            heading-html="{{ $s->section_heading_html }}"
            note-html="{{ $s->section_note_html }}"
        />
    </div>

    <div class="frame">
        <div class="waste-grid">
            @foreach ($cards as $card)
                <div class="wcard">
                    <div class="spec-n">{{ $card->fkko_code }}</div>
                    <x-waste-fig :waste-type="$card" />
                    <h5>{!! $card->title_html !!}</h5>
                    <div class="desc">{{ $card->description }}</div>
                    <span class="cls @if ($card->is_hazard) haz @endif">{{ $card->hazard_class_label }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
