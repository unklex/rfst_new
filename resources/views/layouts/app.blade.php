@php
    $general = app(\App\Settings\GeneralSettings::class);
    $design = app(\App\Settings\DesignSettings::class);
    $integrations = app(\App\Settings\IntegrationSettings::class);

    $favicon = \App\Models\SiteAsset::where('key', 'favicon')->first();
    $faviconWebp = $favicon?->getFirstMediaUrl('image', 'webp') ?: null;
    $faviconOriginal = $favicon?->getFirstMediaUrl('image') ?: null;
    $faviconVersion = $favicon?->updated_at?->timestamp;

    $og = \App\Models\SiteAsset::where('key', 'og_image')->first();
    $ogUrl = $og?->getFirstMediaUrl('image', 'webp') ?: null;
    $ogAlt = trim((string) ($og?->alt ?? '')) !== ''
        ? $og->alt
        : $general->site_name . ' — ' . $general->tagline;

    $pageTitle = $general->site_name . ' — ' . $general->tagline;
    $canonical = url()->current();

    $metrikaId = is_string($integrations->yandex_metrika_id)
        && preg_match('/^\d{5,10}$/', $integrations->yandex_metrika_id) === 1
        ? $integrations->yandex_metrika_id
        : null;
@endphp
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $general->meta_description }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:type" content="website">
<meta property="og:locale" content="ru_RU">
<meta property="og:site_name" content="{{ $general->site_name }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $general->meta_description }}">
<meta property="og:url" content="{{ $canonical }}">
@if ($ogUrl)
<meta property="og:image" content="{{ $ogUrl }}">
<meta property="og:image:width" content="1920">
<meta property="og:image:height" content="1080">
<meta property="og:image:alt" content="{{ $ogAlt }}">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $general->meta_description }}">
@if ($ogUrl)
<meta name="twitter:image" content="{{ $ogUrl }}">
<meta name="twitter:image:alt" content="{{ $ogAlt }}">
@endif

@if ($faviconWebp)
<link rel="icon" type="image/webp" href="{{ $faviconWebp }}{{ $faviconVersion ? '?v=' . $faviconVersion : '' }}">
<link rel="apple-touch-icon" href="{{ $faviconOriginal ?: $faviconWebp }}{{ $faviconVersion ? '?v=' . $faviconVersion : '' }}">
@else
<link rel="icon" href="/favicon.ico">
@endif

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600&family=IBM+Plex+Serif:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&display=swap">

@if (!empty($integrations->turnstile_site_key))
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles

@if ($metrikaId)
{{-- Yandex.Metrika counter: only injected when admin has entered a numeric ID in IntegrationSettings. --}}
<script>
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym({{ $metrikaId }}, "init", { clickmap:true, trackLinks:true, accurateTrackBounce:true });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/{{ $metrikaId }}" style="position:absolute;left:-9999px;" alt=""></div></noscript>
@endif
</head>
<body data-signal="{{ $design->signal }}" data-paper="{{ $design->paper }}" data-head_weight="{{ $design->head_weight }}">

@yield('content')

@livewireScripts
</body>
</html>
