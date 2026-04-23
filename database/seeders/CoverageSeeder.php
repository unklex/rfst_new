<?php

namespace Database\Seeders;

use App\Models\MapPin;
use App\Models\Region;
use Illuminate\Database\Seeder;

class CoverageSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            '01' => 'МОСКВА',
            '02' => 'МО',
            '03' => 'СПБ',
            '04' => 'ЛО',
            '05' => 'КАЛУГА',
            '06' => 'ТУЛА',
            '07' => 'ВОРОНЕЖ',
            '08' => 'КАЗАНЬ',
            '09' => 'ЕКБ',
            '10' => 'ЧЕЛЯБИНСК',
        ];

        $i = 0;
        foreach ($regions as $number => $name) {
            Region::updateOrCreate(
                ['sort' => (++$i) * 10],
                ['number' => $number, 'name' => $name, 'is_active' => true]
            );
        }

        $pins = [
            ['city_name' => 'Москва',        'coordinates' => '55.75 / 37.62', 'position_class' => 'c1'],
            ['city_name' => 'Казань',        'coordinates' => '55.79 / 49.10', 'position_class' => 'c2'],
            ['city_name' => 'Калуга',        'coordinates' => '54.51 / 36.26', 'position_class' => 'c3'],
            ['city_name' => 'Екатеринбург',  'coordinates' => '56.83 / 60.60', 'position_class' => 'c4'],
            ['city_name' => 'Воронеж',       'coordinates' => '51.66 / 39.20', 'position_class' => 'c5'],
        ];

        foreach ($pins as $i => $row) {
            MapPin::updateOrCreate(
                ['sort' => ($i + 1) * 10],
                array_merge($row, ['is_active' => true])
            );
        }
    }
}
