@php
    $about = app(\App\Settings\AboutSettings::class);
    $rows = \App\Models\AboutLedgerRow::ordered()->active()->get();
    $archive = \App\Models\SiteAsset::where('key', 'about_archive')->first();
    $archiveUrl = $archive?->getFirstMediaUrl('image', 'webp') ?: null;
@endphp
<section class="sec about" id="about">
    <div class="frame">
        <x-section-head
            idx="{{ $about->section_index }}"
            kicker="{{ $about->section_kicker }}"
            heading-html="{{ $about->section_heading_html }}"
            note-html="{{ $about->legal_block_html }}"
        />
    </div>

    <div class="frame">
        <div class="about-photo"@if ($archiveUrl) style="background-image:url('{{ $archiveUrl }}');background-size:cover;background-position:center;"@endif>
            <div class="tick"><span></span><span></span><span></span></div>
        </div>
        <div class="about-body">
            <h3>{!! $about->body_heading_html !!}</h3>
            <p>{{ $about->body_paragraph }}</p>

            <div class="ledger">
                @foreach ($rows as $row)
                    <div class="r"><div class="c">{{ $row->code }}</div><div class="t">{!! $row->title_html !!}</div><div class="d">{!! $row->detail_html !!}</div></div>
                @endforeach
            </div>

            <div><a href="{{ $about->cta_url }}" class="btn btn-ghost">{{ $about->cta_label }} <span class="arr">→</span></a></div>
        </div>
    </div>
</section>
