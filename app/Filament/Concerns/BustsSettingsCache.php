<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * Shared afterSave() for every ManageXxxSettings page.
 *
 * Spatie Settings' `SavedSettings` event already re-populates the cache with the
 * fresh values (see SettingsEventSubscriber::onSavedSettings), so an explicit
 * forget() is redundant in the happy path. We still issue one for defence-in-depth
 * and so that editors see changes even if the event listener is ever disabled.
 *
 * The actual cache key produced by Spatie has the form:
 *   "{$prefix}.settings.{$fqcn}"
 * where $prefix comes from config('settings.cache.prefix') and $fqcn is the
 * settings class name (e.g. "App\\Settings\\HeroSettings").
 */
trait BustsSettingsCache
{
    protected function afterSave(): void
    {
        $prefix = config('settings.cache.prefix');
        $key = ($prefix ? "{$prefix}." : '') . 'settings.' . static::getSettings();

        Cache::store(config('settings.cache.store'))->forget($key);
    }
}
