@php
    $c = app(\App\Settings\ContactSettings::class);
@endphp
<section class="contact" id="contact">
    <div class="frame">
        <div class="left">
            <h3>{!! $c->heading_html !!}</h3>
            <div class="info">
                <div class="r"><div class="k">адрес</div><div class="v">{{ $c->address }}</div></div>
                <div class="r"><div class="k">телефон</div><div class="v">{{ $c->phone }}</div></div>
                <div class="r"><div class="k">e-mail</div><div class="v">{!! \Illuminate\Support\Str::of(e($c->email))->replace('@', '<em>@</em>') !!}</div></div>
                <div class="r"><div class="k">часы</div><div class="v">{{ $c->hours }}</div></div>
                <div class="r"><div class="k">мессенджеры</div><div class="v">{{ $c->messengers }}</div></div>
            </div>
        </div>
        <livewire:contact-form />
    </div>
</section>
