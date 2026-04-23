<?php

namespace Database\Seeders;

use App\Models\SiteAsset;
use Illuminate\Database\Seeder;

class SiteAssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['key' => 'hero_bg',        'title' => 'Фоновое изображение hero',           'alt' => ''],
            ['key' => 'about_archive',  'title' => 'Изображение блока «О компании»',    'alt' => 'fig.01 — archive'],
            ['key' => 'quote_reviewer', 'title' => 'Фото автора отзыва',                 'alt' => ''],
            ['key' => 'favicon',        'title' => 'Favicon сайта',                      'alt' => ''],
            ['key' => 'og_image',       'title' => 'Open Graph превью',                  'alt' => ''],
        ];

        foreach ($assets as $row) {
            SiteAsset::updateOrCreate(['key' => $row['key']], $row);
        }
    }
}
