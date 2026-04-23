<?php

namespace Database\Seeders;

use App\Models\AboutLedgerRow;
use Illuminate\Database\Seeder;

class AboutLedgerRowSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'code' => '01 · Лицензия',
                'title_html' => 'Росприроднадзор РФ <em>№ 077</em>',
                'detail_html' => 'III – IV<br>класс',
            ],
            [
                'code' => '02 · Партнёрство',
                'title_html' => 'Золотой партнёр <em>1С-Битрикс</em>',
                'detail_html' => 'сертификат<br>b24-gold',
            ],
            [
                'code' => '03 · Автопарк',
                'title_html' => '14 единиц <em>собственного</em> транспорта',
                'detail_html' => 'ТС с опасным<br>грузом',
            ],
            [
                'code' => '04 · Страхование',
                'title_html' => 'Полис <em>гражданской</em> ответственности',
                'detail_html' => 'до 100<br>млн ₽',
            ],
        ];

        foreach ($rows as $i => $row) {
            AboutLedgerRow::updateOrCreate(
                ['sort' => ($i + 1) * 10],
                array_merge($row, ['is_active' => true])
            );
        }
    }
}
