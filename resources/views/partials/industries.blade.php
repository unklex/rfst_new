@php
    $s = app(\App\Settings\IndustriesSectionSettings::class);
    $rows = \App\Models\Industry::ordered()->active()->get();
@endphp
<section class="sec ind" id="industries">
    <div class="frame">
        <x-section-head
            idx="{{ $s->section_index }}"
            kicker="{{ $s->section_kicker }}"
            heading-html="{{ $s->section_heading_html }}"
            note-html="{{ $s->section_note_html }}"
        />
    </div>

    <div class="frame">
        <table class="ind-table">
            <thead>
                <tr><th>№</th><th>Отрасль</th><th>Характерные отходы</th><th>Класс</th><th>—</th></tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td class="n">{{ $row->number }}</td>
                        <td><div class="title">{!! $row->title_html !!}</div><div class="sub">{{ $row->subtitle }}</div></td>
                        <td class="cls">{{ $row->class_codes }}</td>
                        <td class="vol">{{ $row->class_label }}<small>{{ $row->class_caption }}</small></td>
                        <td class="arr">→</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
