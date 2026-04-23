@props([
    'idx' => '',
    'kicker' => '',
    'headingHtml' => '',
    'noteHtml' => '',
])
<div class="sec-head">
    <div class="idx"><b>{!! $idx !!}</b>{{ $kicker }}</div>
    <h2>{!! $headingHtml !!}</h2>
    <div class="note">{!! $noteHtml !!}</div>
</div>
