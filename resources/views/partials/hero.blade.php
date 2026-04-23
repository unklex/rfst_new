@php
    $hero = app(\App\Settings\HeroSettings::class);
@endphp
<section class="hero">
    <div class="frame">

        <div class="hero-left">
            <div class="hero-slug">
                <div class="code">
                    {!! $hero->ref_code_html !!}
                </div>
                <div class="hazard"><span>{{ $hero->hazard_label }}</span></div>
            </div>

            <h1>{!! $hero->headline_html !!}</h1>

            <p class="lede">
                {!! $hero->lede_html !!}
            </p>

            <div class="ctas">
                <a href="{{ $hero->cta_primary_anchor }}" class="btn btn-signal">{{ $hero->cta_primary_label }} <span class="arr">→</span></a>
                <a href="{{ $hero->cta_secondary_anchor }}" class="btn btn-ghost">{{ $hero->cta_secondary_label }} <span class="arr">→</span></a>
            </div>

            <div class="stamp">
                <div class="sig">{!! $hero->signature_name !!}</div>
                <div class="who">
                    {!! $hero->signature_caption_html !!}
                </div>
            </div>
        </div>

        <div class="hero-right">
            <div class="hero-card">
                <div class="mono">{!! preg_replace('/^(.*?)·/u', '<b>$1</b>·', e($hero->card_a_kicker)) !!}</div>
                <div class="big"><em>{{ $hero->card_a_big_value }}</em><sup>{{ $hero->card_a_big_suffix }}</sup></div>
                <div class="legend"><b>{{ $hero->card_a_label_strong }}</b><span>{{ $hero->card_a_label_text }}</span></div>
                <div class="spec">
                    <div><div class="n">{!! \Illuminate\Support\Str::of(e($hero->card_a_stat1_value))->replace('+', '<sup>+</sup>') !!}</div><div class="k">{{ $hero->card_a_stat1_label }}</div></div>
                    <div><div class="n">{!! \Illuminate\Support\Str::of(e($hero->card_a_stat2_value))->replace('+', '<sup>+</sup>') !!}</div><div class="k">{{ $hero->card_a_stat2_label }}</div></div>
                    <div><div class="n">{!! \Illuminate\Support\Str::of(e($hero->card_a_stat3_value))->replace('+', '<sup>+</sup>') !!}</div><div class="k">{{ $hero->card_a_stat3_label }}</div></div>
                </div>
            </div>
            <div class="hero-card dark">
                <div class="mono">{!! preg_replace('/^(.*?)·/u', '<b>$1</b>·', e($hero->card_b_kicker)) !!}</div>
                <h4>{!! $hero->card_b_title_html !!}</h4>
                <div class="legend"><b>{{ $hero->card_b_license_number }}</b><span>{{ $hero->card_b_license_detail }}</span><b>{{ $hero->card_b_class_label }}</b><span>{{ $hero->card_b_class_value }}</span></div>
            </div>
        </div>
    </div>
</section>
