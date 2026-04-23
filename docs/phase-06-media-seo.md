# Phase 6 — Media & SEO

**Goal**: every image served from the site is a webp variant at an appropriate size; favicon, Open Graph, Twitter Card, and meta description all read from admin-editable settings. An editor uploading a fresh hero background sees it compressed, converted, and appearing in the og:image tag with no developer intervention.

## Prerequisites

- Phase 2 wrote `registerMediaConversions()` on `SiteAsset` and `WasteType`
- Phase 4 has `SiteAssetResource` letting an editor upload files per key
- Phase 3's `layouts/app.blade.php` already reads favicon + og_image + meta_description from settings

## Tasks

### 1. Confirm webp conversions run

Upload a test image to `SiteAsset::hero_bg` via Filament. Inspect:

```bash
ls storage/app/public/*/conversions/
# Expect: *-webp.webp, *-webp_mobile.webp
```

If missing:

- Check `SiteAsset implements HasMedia` (Phase 2 § C, model #17)
- Check `registerMediaConversions()` is declared (Phase 2 § E)
- Check `nonQueued()` is called — otherwise conversions wait for a queue worker
- Install the image driver: `php -r "var_dump(extension_loaded('gd'));"` should return `true`; otherwise install `gd` or `imagick`

### 2. Favicon wiring

In `layouts/app.blade.php` `<head>`:

```blade
@php
    $favicon = \App\Models\SiteAsset::where('key', 'favicon')->first();
    $faviconUrl = $favicon?->getFirstMediaUrl('default', 'webp');
@endphp
@if($faviconUrl)
    <link rel="icon" type="image/webp" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $favicon->getFirstMediaUrl('default') }}">
@endif
```

Alternative (no upload yet): point to `public/favicon.ico` stock Laravel placeholder as fallback.

### 3. Meta description + page title

```blade
@php($general = app(\App\Settings\GeneralSettings::class))
<title>{{ $general->site_name }} — {{ $general->tagline }}</title>
<meta name="description" content="{{ $general->meta_description }}">
```

### 4. Open Graph + Twitter Card

```blade
@php
    $ogImage = \App\Models\SiteAsset::where('key', 'og_image')->first();
    $ogImageUrl = $ogImage?->getFirstMediaUrl('default', 'webp');
@endphp

<meta property="og:type" content="website">
<meta property="og:title" content="{{ $general->site_name }} — {{ $general->tagline }}">
<meta property="og:description" content="{{ $general->meta_description }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:locale" content="ru_RU">
@if($ogImageUrl)
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:image:width" content="1920">
    <meta property="og:image:height" content="1080">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $general->site_name }} — {{ $general->tagline }}">
<meta name="twitter:description" content="{{ $general->meta_description }}">
@if($ogImageUrl)
    <meta name="twitter:image" content="{{ $ogImageUrl }}">
@endif
```

### 5. Image rendering with `<picture>` fallback

In each partial that renders an optional image, use `<picture>` so browsers without webp fall back to the original:

```blade
@php
    $asset = \App\Models\SiteAsset::where('key', 'about_archive')->first();
@endphp
@if($asset && $asset->hasMedia())
    <picture class="about-photo">
        <source type="image/webp" media="(max-width: 768px)" srcset="{{ $asset->getFirstMediaUrl('default', 'webp_mobile') }}">
        <source type="image/webp" srcset="{{ $asset->getFirstMediaUrl('default', 'webp') }}">
        <img src="{{ $asset->getFirstMediaUrl() }}" alt="" loading="lazy">
    </picture>
@else
    {{-- Fallback: original CSS-generated striped placeholder --}}
    <div class="about-photo">
        <div class="tick"><span></span><span></span><span></span></div>
    </div>
@endif
```

Apply the same pattern to:

- `hero.blade.php` if `HeroSettings` has a hero background image slot
- `quote.blade.php` for `quote_reviewer`
- `waste.blade.php` via `<x-waste-fig :type="$waste" />` — per-card image override

### 6. Lazy-loading

All `<img>` tags below the fold get `loading="lazy"`. The hero image (if present) must NOT be lazy — it's above the fold. Add `fetchpriority="high"` on the hero image.

### 7. Sitemap (optional but recommended)

If you want a sitemap, install `spatie/laravel-sitemap` and register a route:

```bash
composer require spatie/laravel-sitemap
```

Create a command `app/Console/Commands/GenerateSitemap.php`:

```php
Sitemap::create()
    ->add(Url::create('/')->setPriority(1.0)->setChangeFrequency('weekly'))
    ->writeToFile(public_path('sitemap.xml'));
```

Single-page site → the sitemap is trivial. Adding news/blog later is where this pays off.

### 8. robots.txt

Edit `public/robots.txt`:

```
User-agent: *
Allow: /
Disallow: /admin
Disallow: /livewire
Sitemap: https://crypton.example.com/sitemap.xml
```

### 9. Optional: image optimization pipeline at upload

Spatie Media Library supports pre-save optimizers. In `config/media-library.php`:

```php
'image_optimizers' => [
    Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => ['-m85', '--strip-all', '--all-progressive'],
    Spatie\ImageOptimizer\Optimizers\Pngquant::class  => ['--force'],
    Spatie\ImageOptimizer\Optimizers\Gifsicle::class  => ['-b', '-O3'],
    Spatie\ImageOptimizer\Optimizers\Svgo::class      => ['--disable={cleanupIDs,removeViewBox}'],
    Spatie\ImageOptimizer\Optimizers\Cwebp::class     => ['-m 6', '-pass 10', '-mt', '-q 90'],
],
```

Install native binaries if present on the host (`choco install jpegoptim pngquant cwebp` on Windows, or skip — conversions still work without them).

## Verification

- [ ] Upload a JPG to `SiteAsset::hero_bg` → `storage/app/public/*/conversions/*-webp.webp` exists + `*-webp_mobile.webp` exists
- [ ] Upload a JPG to `SiteAsset::favicon` → 180px webp variant exists, no mobile variant
- [ ] Upload a JPG to `WasteType::image` (on a specific card) → 640px webp + 320px thumb exist
- [ ] View homepage source: `<link rel="icon">` points to the uploaded favicon webp
- [ ] View homepage source: `<meta name="description">` contains the text from `GeneralSettings::meta_description`
- [ ] View homepage source: `<meta property="og:image">` points to the og_image webp
- [ ] Edit `GeneralSettings::meta_description` in admin → save → reload → meta updates
- [ ] Open DevTools → Network tab → filter by `img` → every image request returns `Content-Type: image/webp`
- [ ] Lighthouse run on `/` scores ≥ 90 on Performance and 100 on Best Practices (basic sanity)
- [ ] `public/robots.txt` accessible at `/robots.txt`
- [ ] Twitter Card Validator (`cards-dev.twitter.com/validator`) renders the preview correctly — or test locally: `curl -A "Twitterbot" http://127.0.0.1:8000/ | grep twitter:`
- [ ] Facebook OG Debugger (`developers.facebook.com/tools/debug/`) renders the preview correctly — optional, requires public URL

## Common pitfalls

- **Webp not generated**: GD/Imagick missing webp support. Check `php -r "print_r(gd_info());"` for `WebP Support => true`.
- **`hasMedia()` returns false despite upload succeeding**: collection name mismatch. `getFirstMediaUrl()` defaults to `default` collection; if the uploader used a named collection, pass that name: `getFirstMediaUrl('waste_fig')`.
- **Favicon not updating**: browser cache. Hard reload (Ctrl+Shift+R) or add `?v={{ $favicon->updated_at->timestamp }}` to the URL.
- **og:image broken on production**: `getFirstMediaUrl` returns a relative path if `storage:link` wasn't run. Configure `config/media-library.php → disk_name = 'public'` and ensure `APP_URL` is set.
- **Conversion files orphaned after deleting a SiteAsset**: Spatie cleans them up automatically; if not, run `php artisan media-library:clean`.

## Next

Phase 7 runs production build + the full 14-point verification checklist from the master plan.
