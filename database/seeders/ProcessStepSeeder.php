<?php

namespace Database\Seeders;

use App\Models\ProcessStep;
use Illuminate\Database\Seeder;

class ProcessStepSeeder extends Seeder
{
    public function run(): void
    {
        $steps = [
            ['number' => '01', 'title' => 'Заявка', 'description' => 'Звонок или форма. Первичная квалификация по ФККО.',          'meta_label' => 'время', 'meta_value' => '≤ 30 мин'],
            ['number' => '02', 'title' => 'Аудит',  'description' => 'Выезд инженера, классификация отходов, замеры объёмов.',    'meta_label' => 'время', 'meta_value' => '1–3 дня'],
            ['number' => '03', 'title' => 'Договор','description' => 'Фиксированные ставки, прозрачный график, штрафы на нашей стороне.', 'meta_label' => 'время', 'meta_value' => '48 часов'],
            ['number' => '04', 'title' => 'Вывоз',  'description' => 'Собственный лицензированный транспорт, GPS-трекинг.',       'meta_label' => 'окно',  'meta_value' => '2–24ч'],
            ['number' => '05', 'title' => 'Отчёт',  'description' => 'Акты утилизации, паспорта, отчёт для Росприроднадзора.',     'meta_label' => 'срок',  'meta_value' => '7 дней'],
        ];

        foreach ($steps as $i => $row) {
            ProcessStep::updateOrCreate(
                ['sort' => ($i + 1) * 10],
                array_merge($row, ['is_active' => true])
            );
        }
    }
}
