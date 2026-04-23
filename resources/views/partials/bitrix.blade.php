@php
    $b = app(\App\Settings\BitrixSettings::class);
    $features = \App\Models\BitrixFeature::ordered()->active()->get();
    $columns = \App\Models\BitrixMockColumn::ordered()->active()->with(['cards' => fn ($q) => $q->where('is_active', true)->orderBy('sort')])->get();
    $accentMap = ['signal' => '', 'ink' => 'i', 'green' => 'g'];
@endphp
<section class="bitrix" id="bitrix">
    <div class="frame">
        <div class="bitrix-intro">
            <div class="kick">{{ $b->kicker }}</div>
            <h3>{!! $b->heading_html !!}</h3>
            <p>{{ $b->paragraph }}</p>

            <div class="bitrix-feat">
                @foreach ($features as $f)
                    <div class="r"><div class="n">{{ $f->number }}</div><div class="t">{!! $f->title_html !!}<small>{{ $f->subtitle }}</small></div><div class="arr">→</div></div>
                @endforeach
            </div>

            <div><a href="{{ $b->cta_url }}" class="btn btn-signal">{{ $b->cta_label }} <span class="arr">→</span></a></div>
        </div>

        <div class="bitrix-mock">
            <div class="mock-window">
                <div class="mock-bar">
                    <div class="dots"><span></span><span></span><span></span></div>
                    <div class="url">{{ $b->mock_url }}</div>
                    <div>{{ $b->mock_version }}</div>
                </div>
                <div class="mock-body">
                    @foreach ($columns as $col)
                        <div class="mock-col">
                            <h6>{{ $col->title }} <b>{{ $col->badge }}</b></h6>
                            @foreach ($col->cards as $card)
                                @php $accent = $accentMap[$card->accent] ?? ''; @endphp
                                <div class="mock-card @if ($accent) {{ $accent }} @endif"><b>{{ $card->label }}</b><div class="v">{!! $card->value_html !!}</div></div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div class="mock-foot"><span>{{ $b->mock_footer_left }}</span><span>{!! $b->mock_footer_right_html !!}</span></div>
            </div>
            <div class="caption">{{ $b->caption }}</div>
        </div>
    </div>
</section>
