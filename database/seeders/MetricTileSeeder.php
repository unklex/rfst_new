<?php

namespace Database\Seeders;

use App\Models\MetricTile;
use Illuminate\Database\Seeder;

class MetricTileSeeder extends Seeder
{
    public function run(): void
    {
        $tiles = [
            [
                'key_label' => 'A.',
                'key_strong' => 'Операции',
                'value_html' => '2&nbsp;840<sup>+</sup>',
                'caption_html' => 'плановых и срочных <span>вывозов за 2025 г.</span>',
            ],
            [
                'key_label' => 'B.',
                'key_strong' => 'Объём',
                'value_html' => '<em>18.4</em><sup>кт</sup>',
                'caption_html' => 'тонн отходов <span>утилизировано с 2014 г.</span>',
            ],
            [
                'key_label' => 'C.',
                'key_strong' => 'Экономия',
                'value_html' => '–28<sup>%</sup>',
                'caption_html' => 'средняя экономия <span>расходов у клиентов</span>',
            ],
            [
                'key_label' => 'D.',
                'key_strong' => 'Штрафы',
                'value_html' => '<em>0</em>',
                'caption_html' => 'нарушений лицензии <span>с момента основания</span>',
            ],
        ];

        foreach ($tiles as $i => $row) {
            MetricTile::updateOrCreate(
                ['sort' => ($i + 1) * 10],
                array_merge($row, ['is_active' => true])
            );
        }
    }
}
