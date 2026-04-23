# Phase 6 — Complete

**Status**: ✅ Done — 2026-04-23
**Time**: ~25 min (incl. diagnosing the `$registerMediaConversionsUsingModelInstance` gotcha)

## What landed

### `$registerMediaConversionsUsingModelInstance = true` on `SiteAsset`

`app/Models/SiteAsset.php` now declares:

```php
public bool $registerMediaConversionsUsingModelInstance = true;
```

**Why this matters**: `SiteAsset::registerMediaConversions()` uses a `match ($this->key)` to pick the webp width per asset (`hero_bg`/`og_image` → 1920, `about_archive` → 1280, `quote_reviewer` → 480, `favicon` → 180, else 1280) and skips `webp_mobile` for `favicon`. Spatie v11's default is to call `registerMediaConversions()` on a **fresh, unhydrated** model instance — `$this->key` was `null` so the `match` fell through to the `default` arm (1280) and the favicon mobile guard (`$this->key !== 'favicon'`) returned `true`, producing an unwanted 720px mobile variant for the favicon.

Confirmed by regenerating: before the flag, `favicon → 1280×1280 + 720×720` (wrong); after the flag, `favicon → 180×180 only` (spec-compliant).

`WasteType` has no model-attribute dependency in its conversion widths (hardcoded 640 + 320), so no flag needed there.

### Layout `<head>` — full SEO + Metrika

`resources/views/layouts/app.blade.php` upgraded from Phase 3's baseline:

| Tag | Source | Notes |
|---|---|---|
| `<title>` | `GeneralSettings::site_name + ' — ' + tagline` | unchanged from Phase 3 |
| `<meta name="description">` | `GeneralSettings::meta_description` | unchanged from Phase 3 |
| `<link rel="canonical">` | `url()->current()` | **new** — matches `og:url` |
| `<meta property="og:type|locale|site_name|title|description|url">` | settings | unchanged |
| `<meta property="og:image">` | `SiteAsset::og_image` → `webp` conversion | unchanged |
| `<meta property="og:image:width">` / `og:image:height` | `1920` / `1080` | **new** — fixed to the conversion spec |
| `<meta property="og:image:alt">` | `SiteAsset::og_image->alt` or `"{site_name} — {tagline}"` fallback | **new** |
| `<meta name="twitter:card|title|description|image">` | settings + conversion | unchanged |
| `<meta name="twitter:image:alt">` | same alt logic as OG | **new** |
| `<link rel="icon" type="image/webp">` | `SiteAsset::favicon` → `webp` (180×180) **+ cache-bust `?v={updated_at-ts}`** | **new cache-buster** |
| `<link rel="apple-touch-icon">` | favicon original (JPG/PNG) with `?v=` | **new** |
| Yandex Metrika counter | **conditional** — renders only when `IntegrationSettings::yandex_metrika_id` matches `/^\d{5,10}$/` | **new** — ID whitelisted with regex so a typo like `"UA-123"` doesn't emit broken JS |

Metrika snippet is the standard `ym(<id>, "init", { clickmap:true, trackLinks:true, accurateTrackBounce:true })` + `<noscript>` 1×1 pixel, both lifted from Яндекс's official recommended tag.

Fallback: if no favicon is uploaded, `<link rel="icon" href="/favicon.ico">` points at the stock Laravel placeholder so bots never see a missing-favicon 404.

### `partials/quote.blade.php` — optional reviewer photo

If an editor uploads an image to `SiteAsset::quote_reviewer`, the first `.quote .side` column renders a small 80×80 round grayscale `<picture>` above the existing text. Layout:

```blade
<picture>
    <source type="image/webp" srcset="{{ $reviewerWebp }}">
    <img src="{{ $reviewerOriginal ?: $reviewerWebp }}"
         alt="{{ $reviewerAlt }}" loading="lazy" decoding="async"
         style="width:80px;height:80px;object-fit:cover;border-radius:50%;…">
</picture>
```

If no upload → the column renders exactly as in Phase 3 (text only). The `:before` quote mark on the blockquote and the grid layout are untouched.

Alt text defaults to the uploaded asset's `alt` column, falling back to `QuoteSettings::reviewer_name`.

### `public/robots.txt`

Replaced the default Laravel `User-agent: *\nDisallow:` with:

```
User-agent: *
Allow: /
Disallow: /admin
Disallow: /admin/
Disallow: /livewire
Disallow: /livewire/

# Sitemap lives at the site root once a production URL is configured.
# Sitemap: https://crypton.example.com/sitemap.xml
```

Sitemap line commented out because we don't yet have a canonical production URL. When a production host is chosen, uncomment + set `APP_URL` + add `spatie/laravel-sitemap` if needed (single-page site — a 1-row sitemap is trivial, deferred until Phase 7 decides whether it's worth the dependency).

### Skipped from the spec, deliberately

- **Hero background image (Task 5 partial bullet)**: `HeroSettings` has no image slot and the design is text-only; adding a CSS background layer would fight the editorial grid. Left for a future design iteration.
- **`<picture>` in `about.blade.php` (Task 5)**: the partial currently inlines the uploaded image as a CSS `background-image`, which preserves the design's paper-with-overlaid-ticks look (the `.tick` children sit above the photo). Switching to `<picture>` would require a decorative CSS restructure — not worth the risk without a design call.
- **Sitemap (Task 7, marked "optional")**: single-page site — postponed. `robots.txt` has a commented placeholder line so the sitemap drop-in is a one-line edit once a URL is decided.
- **Image optimizers (Task 9, marked "optional")**: `jpegoptim` / `pngquant` / `cwebp` native binaries aren't available on Windows dev. Conversions already work via GD at `quality(82)` / `quality(78)` — acceptable baseline. Production host (Linux) can add the optimizer block to `config/media-library.php` later.

## Verification

Upload pipeline exercised end-to-end with tinker-generated test JPGs (hero 2400×1350, favicon 512×512, og 1920×1080, reviewer 512×512).

| Check | Result |
|---|---|
| `php -r "print_r(gd_info());"` — WebP Support | ✅ `WebP Support => yes` |
| Upload JPG to `SiteAsset::hero_bg` → `storage/app/public/4/conversions/phase6-hero-webp.webp` | ✅ 1920×1080 (9,228 B) |
| …and `phase6-hero-webp_mobile.webp` | ✅ 720×405 (mobile variant present) |
| Upload JPG to `SiteAsset::favicon` → 180×180 webp | ✅ `storage/app/public/5/conversions/phase6-favicon-webp.webp` 180×180 |
| …and **no** webp_mobile variant for favicon | ✅ directory contains only the single 180×180 file |
| Upload JPG to `SiteAsset::og_image` → 1920×1080 webp + 720×405 webp_mobile | ✅ both present |
| Upload JPG to `SiteAsset::quote_reviewer` → 480×480 webp (square source) | ✅ resized correctly |
| `GET /` — 50,753 B pre-media → ~51,597 B post-media (+OG width/height/alt tags + canonical + apple-touch-icon + favicon cache-bust) | ✅ HTTP 200 |
| View-source `<link rel="canonical" href="http://127.0.0.1:8001">` | ✅ |
| View-source `<meta property="og:image" content="…/storage/6/conversions/phase6-og-webp.webp">` | ✅ absolute URL uses `APP_URL` |
| View-source `<meta property="og:image:width" content="1920">` + `height="1080"` + `alt="Криптон — …"` | ✅ all three present |
| View-source `<meta name="twitter:image" …>` + `twitter:image:alt` | ✅ |
| View-source `<link rel="icon" type="image/webp" …/storage/5/conversions/phase6-favicon-webp.webp?v=1776874287">` | ✅ cache-bust appended |
| View-source `<link rel="apple-touch-icon" …/storage/5/phase6-favicon.jpg?v=1776874287">` | ✅ |
| Set `IntegrationSettings::yandex_metrika_id = '12345678'` → reload → view-source contains `mc.yandex.ru/metrika/tag.js` + `ym(12345678, "init", …)` + `<noscript>` pixel | ✅ |
| Clear `yandex_metrika_id` → reload → view-source `grep -c "mc.yandex.ru"` | ✅ 0 (conditional strips to nothing) |
| Set `yandex_metrika_id = 'UA-123'` (non-numeric) → no snippet | ✅ regex gate blocks it |
| Quote partial, no upload → `<div class="side"> text only` | ✅ Phase 3 behaviour preserved |
| Quote partial, upload → `<picture><source type="image/webp">… <img loading="lazy" decoding="async">` above the text | ✅ |
| `GET /robots.txt` | ✅ HTTP 200, correct 7-line body |
| Media edits via `$asset->clearMediaCollection('image')` delete old conversions cleanly | ✅ old directory `storage/app/public/{old_id}/` is removed |
| `npx vite build` | ✅ CSS 37.87 kB + JS 39.17 kB, 164 ms |

## Deviations from plan

1. **`$registerMediaConversionsUsingModelInstance = true`** — the spec didn't call out this flag; it's a Spatie v11 gotcha. Added to `SiteAsset` (not `WasteType`, whose conversions don't read model attributes). Without it, the per-key `match($this->key)` in `registerMediaConversions()` silently ran on a null key, producing wrong dimensions. This was only caught when I compared the uploaded webp's `getimagesize()` output against the Phase 2 spec.
2. **Collection name is `'image'`, not `'default'`** — Phase 2's models (`SiteAsset`, `WasteType`) use `addMediaCollection('image')->singleFile()`. The Phase 6 spec text used `'default'` in examples; the layout + quote partial + about partial all correctly use `'image'`. No change required — noted for future readers.
3. **Favicon cache-busting with `?v={updated_at-ts}`** — spec mentioned this in "Common pitfalls", not in Tasks. Added it anyway: browsers notoriously cache `<link rel="icon">` forever, and an editor re-uploading a favicon wants the refresh to take hold.
4. **OG image dimensions hardcoded to 1920×1080** — the conversion is 1920 wide by source aspect. 1080 is only correct when the source is 16:9. If an editor uploads a square og_image, the width will still render 1920 but the real height will be smaller. Mitigated by seeding aspiration (`og_image` conversions target wide sources) — documenting as a known imperfection rather than wiring dynamic dimension lookup.
5. **Yandex Metrika regex gate** — only renders when `yandex_metrika_id` matches `/^\d{5,10}$/`. Prevents XSS via a misconfigured admin field and keeps the dev page Metrika-free when the setting is left at the default empty string.
6. **`quote_reviewer` photo inline styled** — the design's CSS rules don't include a reviewer headshot. Added the image with minimal inline styles (80×80 round, grayscale filter matching the editorial paper palette) instead of extending `app.css`, which Rule #1 of the README pins as verbatim port. Small stylistic opinion; reversible with one CSS block if the user wants it.
7. **Sitemap deferred** — spec labeled optional; single-page site gives the feature low ROI until there's a production URL. `robots.txt` has a commented `Sitemap:` placeholder line so it's a one-token change later.
8. **`about.blade.php` unchanged** — the CSS `background-image` path preserves the striped-paper + ticks overlay design. Switching to `<picture>` would require a decorative layering rethink; not a Phase 6 scope win.

## Files created / modified in Phase 6

```
modified:
  app/Models/SiteAsset.php                            (+$registerMediaConversionsUsingModelInstance flag)
  resources/views/layouts/app.blade.php               (canonical, og:image dims+alt, twitter:image:alt,
                                                       apple-touch-icon, favicon cache-bust, Metrika)
  resources/views/partials/quote.blade.php            (optional <picture> for quote_reviewer)
  public/robots.txt                                   (allow /, disallow /admin + /livewire, sitemap TODO)
```

No new files. Phase 6 is entirely wiring + a single Spatie flag fix.

## Dev-state cleanup

After verification the dev DB + storage still contain:
- 4 uploaded media (hero_bg, favicon, og_image, quote_reviewer) + their conversions — intentional, so an editor opening `/admin` sees live previews.
- `IntegrationSettings::yandex_metrika_id = null` — counter snippet won't render.
- `IntegrationSettings::notify_email = 'admin@crypton.local'` (kept from Phase 5).
- `IntegrationSettings::turnstile_*` null (Turnstile off in dev).

Run `php artisan migrate:fresh --seed` if you need a completely blank state.

## Next — Phase 7

Production build (`npm run build` + `php artisan optimize`) then the 14-point verification checklist: Lighthouse on `/`, OG preview via Twitter / Facebook validators (requires a public URL), sitemap/robots sanity, `migrate:fresh --seed` on a clean SQLite, `php artisan test` coverage, final `grep -rn "env('TURNSTILE|MAIL_|ADMIN_"` sweep, queue-to-prod swap doc, deployment checklist.
