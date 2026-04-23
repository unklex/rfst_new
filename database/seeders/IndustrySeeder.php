<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'number' => '01',
                'title_html' => 'Металлообработка и <em>тяжёлая промышленность</em>',
                'subtitle' => 'Шлаки, гальванические стоки, СОЖ, металлолом',
                'class_codes' => 'шлаки · стоки · брак',
                'class_label' => 'III–IV',
                'class_caption' => 'отраслевой',
            ],
            [
                'number' => '02',
                'title_html' => 'Офисы и <em>ритейл</em>',
                'subtitle' => 'Макулатура, картон, плёнка, просрочка',
                'class_codes' => 'бумага · пластик',
                'class_label' => 'IV–V',
                'class_caption' => 'лёгкие',
            ],
            [
                'number' => '03',
                'title_html' => 'Оборонный <em>комплекс</em>',
                'subtitle' => 'Жидкие, ЛКМ, растворители, производственные',
                'class_codes' => 'жидкие · ЛКМ',
                'class_label' => 'III',
                'class_caption' => 'опасные',
            ],
            [
                'number' => '04',
                'title_html' => 'Фармацевтика и <em>клиники</em>',
                'subtitle' => 'Препараты, ампулы, упаковка, медотходы',
                'class_codes' => 'фарм. · ампулы',
                'class_label' => 'III',
                'class_caption' => 'класс A–B',
            ],
            [
                'number' => '05',
                'title_html' => 'Транспорт и <em>логистика</em>',
                'subtitle' => 'Аккумуляторы, масла, шины, фильтры',
                'class_codes' => 'АКБ · масла',
                'class_label' => 'III–IV',
                'class_caption' => 'автопарк',
            ],
            [
                'number' => '06',
                'title_html' => 'Государственный <em>сектор</em>',
                'subtitle' => 'Учреждения, ведомства, инфраструктура',
                'class_codes' => 'смешанные',
                'class_label' => 'III–V',
                'class_caption' => '44-ФЗ',
            ],
        ];

        foreach ($rows as $i => $row) {
            Industry::updateOrCreate(
                ['sort' => ($i + 1) * 10],
                array_merge($row, ['is_active' => true])
            );
        }
    }
}
