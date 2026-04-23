<?php

namespace Database\Seeders;

use App\Models\TickerItem;
use Illuminate\Database\Seeder;

class TickerItemSeeder extends Seeder
{
    public function run(): void
    {
        $labels = [
            '— III КЛАСС —',
            'IV КЛАСС',
            'СБОР · ТРАНСПОРТ · УТИЛИЗАЦИЯ',
            'БИТРИКС24 / ЭКОЛОГИЯ',
            'АВТОМАТИЗАЦИЯ ДОКУМЕНТООБОРОТА',
            'CRM ДЛЯ ОТХОДООБРАЗОВАТЕЛЕЙ',
        ];

        foreach ($labels as $i => $label) {
            TickerItem::updateOrCreate(
                ['sort' => ($i + 1) * 10],
                ['label' => $label, 'is_active' => true]
            );
        }
    }
}
