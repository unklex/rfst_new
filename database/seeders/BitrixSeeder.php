<?php

namespace Database\Seeders;

use App\Models\BitrixFeature;
use App\Models\BitrixMockCard;
use App\Models\BitrixMockColumn;
use Illuminate\Database\Seeder;

class BitrixSeeder extends Seeder
{
    public function run(): void
    {
        // ── Features (A.01 … A.04) ──
        $features = [
            ['number' => 'A.01', 'title_html' => 'Документооборот <em>в цифре</em>', 'subtitle' => 'Паспорта, договоры, акты утилизации — без бумаги'],
            ['number' => 'A.02', 'title_html' => 'Управление <em>проектами</em> и задачами', 'subtitle' => 'Agile, Канбан, диаграммы Ганта'],
            ['number' => 'A.03', 'title_html' => 'Единое <em>инфополе</em> команды', 'subtitle' => 'Чаты, видео, календари, шаблоны'],
            ['number' => 'A.04', 'title_html' => 'CRM для <em>отходообразователей</em>', 'subtitle' => 'Клиенты, повторные вывозы, ставки, скидки'],
        ];

        foreach ($features as $i => $row) {
            BitrixFeature::updateOrCreate(
                ['sort' => ($i + 1) * 10],
                array_merge($row, ['url' => '#', 'is_active' => true])
            );
        }

        // ── Kanban columns + cards ──
        $columns = [
            [
                'title' => 'Новые', 'badge' => '07',
                'cards' => [
                    ['accent' => 'signal', 'label' => 'ЗАО · Металл-Сервис', 'value_html' => 'III класс — 2.5 т'],
                    ['accent' => 'ink',    'label' => 'ООО · Аптека-7',      'value_html' => 'фарм. — 120 кг'],
                    ['accent' => 'green',  'label' => '«Офис-Плюс»',         'value_html' => 'бумага — 800 кг'],
                ],
            ],
            [
                'title' => 'В работе', 'badge' => '03',
                'cards' => [
                    ['accent' => 'signal', 'label' => 'ПАО · Оборонпром',  'value_html' => 'жидк. · срочно'],
                    ['accent' => 'ink',    'label' => 'ООО · Криптон-Л',   'value_html' => 'IV класс — 1.1 т'],
                ],
            ],
            [
                'title' => 'Закрыто', 'badge' => '12',
                'cards' => [
                    ['accent' => 'green', 'label' => 'АО · Рыбхоз',            'value_html' => 'утилизировано'],
                    ['accent' => 'green', 'label' => 'МУП · Горводоканал',    'value_html' => 'акт подписан'],
                ],
            ],
        ];

        foreach ($columns as $ci => $colData) {
            $column = BitrixMockColumn::updateOrCreate(
                ['sort' => ($ci + 1) * 10],
                ['title' => $colData['title'], 'badge' => $colData['badge'], 'is_active' => true]
            );

            foreach ($colData['cards'] as $cardIdx => $card) {
                BitrixMockCard::updateOrCreate(
                    ['column_id' => $column->id, 'sort' => ($cardIdx + 1) * 10],
                    array_merge($card, ['is_active' => true])
                );
            }
        }
    }
}
