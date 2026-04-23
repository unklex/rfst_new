<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'title_html' => 'Старт',
                'badge' => 'MVP',
                'price_main' => '290',
                'price_suffix' => '₽',
                'price_caption' => 'тыс. · 14 рабочих дней',
                'features' => [
                    'Настройка корпоративного портала',
                    'CRM под профиль отходообразователя',
                    '5 шаблонов документов',
                    'Интеграция с почтой и телефонией',
                    '3 месяца сопровождения',
                ],
                'cta_label' => 'Обсудить пакет',
                'is_highlighted' => false,
            ],
            [
                'title_html' => '<em>Профи</em>',
                'badge' => 'хит',
                'price_main' => '590',
                'price_suffix' => '₽',
                'price_caption' => 'тыс. · 28 рабочих дней',
                'features' => [
                    'Всё из пакета «Старт»',
                    'Автоматизация паспортов отходов',
                    'Интеграция с 1С и ЭДО',
                    'Диспетчеризация вывозов',
                    'Отчётность для Росприроднадзора',
                    '6 месяцев сопровождения',
                ],
                'cta_label' => 'Обсудить пакет',
                'is_highlighted' => true,
            ],
            [
                'title_html' => 'Корпорация',
                'badge' => 'enterprise',
                'price_main' => 'от&thinsp;1.2',
                'price_suffix' => 'млн ₽',
                'price_caption' => 'по ТЗ · 6–12 недель',
                'features' => [
                    'Всё из пакета «Профи»',
                    'Многокомпанейность и филиалы',
                    'Роли, регламенты, SLA',
                    'Кастомные разработки',
                    'Выделенный архитектор',
                    '12 месяцев сопровождения',
                ],
                'cta_label' => 'Запросить ТЗ',
                'is_highlighted' => false,
            ],
        ];

        foreach ($plans as $i => $row) {
            Plan::updateOrCreate(
                ['sort' => ($i + 1) * 10],
                array_merge($row, ['cta_url' => '#contact', 'is_active' => true])
            );
        }
    }
}
