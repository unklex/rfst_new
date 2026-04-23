<?php

namespace Database\Seeders;

use App\Models\FooterColumn;
use App\Models\FooterLink;
use Illuminate\Database\Seeder;

class FooterSeeder extends Seeder
{
    public function run(): void
    {
        $columns = [
            [
                'heading' => 'Услуги',
                'links' => [
                    ['label' => 'Сбор отходов',          'url' => '#'],
                    ['label' => 'Транспортирование',     'url' => '#'],
                    ['label' => 'Обезвреживание',        'url' => '#'],
                    ['label' => 'Внедрение Битрикс24',   'url' => '#'],
                    ['label' => 'Аудит и оптимизация',   'url' => '#'],
                ],
            ],
            [
                'heading' => 'Компания',
                'links' => [
                    ['label' => 'О нас',                       'url' => '#about'],
                    ['label' => 'Лицензии и сертификаты',      'url' => '#'],
                    ['label' => 'Кейсы и отчёты',              'url' => '#'],
                    ['label' => 'Блог',                        'url' => '#'],
                    ['label' => 'Карьера',                     'url' => '#'],
                ],
            ],
            [
                'heading' => 'Связь',
                'links' => [
                    ['label' => '+7 910 542 10 10',                 'url' => 'tel:+79105421010'],
                    ['label' => 'info@rf-st.ru',                    'url' => 'mailto:info@rf-st.ru'],
                    ['label' => 'Миклухо-Маклая, 34 · Москва',      'url' => '#'],
                    ['label' => 'Telegram ↗',                       'url' => '#',    'is_external' => true],
                    ['label' => 'WhatsApp ↗',                       'url' => '#',    'is_external' => true],
                ],
            ],
        ];

        foreach ($columns as $ci => $colData) {
            $column = FooterColumn::updateOrCreate(
                ['sort' => ($ci + 1) * 10],
                ['heading' => $colData['heading'], 'is_active' => true]
            );

            foreach ($colData['links'] as $li => $link) {
                FooterLink::updateOrCreate(
                    ['footer_column_id' => $column->id, 'sort' => ($li + 1) * 10],
                    array_merge(
                        ['is_external' => false, 'is_active' => true],
                        $link,
                    )
                );
            }
        }
    }
}
