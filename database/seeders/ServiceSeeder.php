<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'line_label' => 'линия А',
                'index_label' => '01 / 03',
                'symbol' => '⟐',
                'title_html' => 'Сбор, транспорт и <em>обезвреживание</em> отходов III–IV класса',
                'description' => 'Разовый вывоз, плановый график или срочный вызов. Собственный лицензированный транспорт и полный пакет закрывающих документов.',
                'spec_rows' => [
                    ['k' => 'отклик', 'v_html' => 'до <em>24ч</em>'],
                    ['k' => 'парк', 'v_html' => '14 ТС'],
                    ['k' => 'документы', 'v_html' => 'полный пак.'],
                ],
                'footer_code' => 'A-001',
                'cta_url' => '#',
                'is_featured' => false,
            ],
            [
                'line_label' => 'линия B',
                'index_label' => '02 / 03',
                'symbol' => '₽',
                'title_html' => 'Аудит и <em>оптимизация</em> расходов на отходы',
                'description' => 'Анализируем потоки, переподписываем договоры, пересогласовываем тарифы. Платим только за подтверждённую экономию.',
                'spec_rows' => [
                    ['k' => 'срок аудита', 'v_html' => '10–14 дн.'],
                    ['k' => 'эконом.', 'v_html' => '<em>–18…32%</em>'],
                    ['k' => 'модель', 'v_html' => 'success fee'],
                ],
                'footer_code' => 'B-002',
                'cta_url' => '#',
                'is_featured' => false,
            ],
            [
                'line_label' => 'линия C',
                'index_label' => '03 / 03',
                'symbol' => 'B',
                'title_html' => 'Внедрение <em>Битрикс24</em> для экологического сектора',
                'description' => 'Корпоративный портал и CRM под специфику отходообразования: паспорта, лицензии, транспорт, Росприроднадзор, плановые вывозы — в едином окне.',
                'spec_rows' => [
                    ['k' => 'срок', 'v_html' => '<em>от 14 дн.</em>'],
                    ['k' => 'пакеты', 'v_html' => '3 тарифа'],
                    ['k' => 'тип', 'v_html' => 'SaaS/Box'],
                ],
                'footer_code' => 'C-003',
                'cta_url' => '#',
                'is_featured' => true,
            ],
        ];

        foreach ($services as $i => $row) {
            Service::updateOrCreate(
                ['sort' => ($i + 1) * 10],
                array_merge($row, ['is_active' => true])
            );
        }
    }
}
