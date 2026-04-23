@php
    $items = \App\Models\TickerItem::ordered()->active()->get();
@endphp
<div class="tech-strip">
    <div class="track">
        @for ($pass = 0; $pass < 2; $pass++)
            @foreach ($items as $item)
                <span>{{ $item->label }}</span>
                <span class="d">◆</span>
            @endforeach
        @endfor
    </div>
</div>
