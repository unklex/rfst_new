<?php

namespace Database\Seeders;

use App\Models\NavItem;
use Illuminate\Database\Seeder;

class NavItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['label' => 'Главная',    'anchor' => '#top'],
            ['label' => 'Компания',   'anchor' => '#about'],
            ['label' => 'Услуги',     'anchor' => '#services'],
            ['label' => 'Битрикс24',  'anchor' => '#bitrix'],
            ['label' => 'Отрасли',    'anchor' => '#industries'],
            ['label' => 'География',  'anchor' => '#cov'],
            ['label' => 'Контакты',   'anchor' => '#contact'],
        ];

        foreach ($items as $i => $row) {
            NavItem::updateOrCreate(
                ['sort' => ($i + 1) * 10],
                [
                    'label' => $row['label'],
                    'anchor' => $row['anchor'],
                    'is_external' => false,
                    'is_active' => true,
                ]
            );
        }
    }
}
