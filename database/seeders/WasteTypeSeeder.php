<?php

namespace Database\Seeders;

use App\Models\WasteType;
use Illuminate\Database\Seeder;

class WasteTypeSeeder extends Seeder
{
    public function run(): void
    {
        $cards = [
            ['fkko_code' => 'код фкко · 9 41 000', 'glyph' => 'М', 'title_html' => 'Минеральные <em>отходы</em>',          'description' => 'Шламы, грунты, строительный мусор, минеральные сорбенты.',     'hazard_class_label' => 'IV класс',    'is_hazard' => false],
            ['fkko_code' => 'код фкко · 3 02 000', 'glyph' => 'П', 'title_html' => 'Производственные <em>остатки</em>',    'description' => 'Технологический брак, обрезки, смазочные материалы.',          'hazard_class_label' => 'III класс',   'is_hazard' => true],
            ['fkko_code' => 'код фкко · 4 81 000', 'glyph' => 'Э', 'title_html' => 'Электронный <em>лом</em>',             'description' => 'Серверы, кабели, платы, оргтехника, АКБ и батареи.',           'hazard_class_label' => 'III–IV',      'is_hazard' => true],
            ['fkko_code' => 'код фкко · 4 71 000', 'glyph' => 'Ф', 'title_html' => 'Фармацевтические <em>отходы</em>',     'description' => 'Просроченные препараты, ампулы, упаковка, мед. отходы.',       'hazard_class_label' => 'III класс',   'is_hazard' => true],
            ['fkko_code' => 'код фкко · 4 61 000', 'glyph' => 'Ж', 'title_html' => 'Жидкие отходы и <em>стоки</em>',       'description' => 'Нефтешламы, растворы, ЛКМ, растворители, эмульсии.',           'hazard_class_label' => 'III класс',   'is_hazard' => true],
            ['fkko_code' => 'код фкко · 4 05 000', 'glyph' => 'Б', 'title_html' => 'Макулатура и <em>вторсырьё</em>',      'description' => 'Бумага, картон, плёнка, пластики, стекло.',                    'hazard_class_label' => 'IV–V',        'is_hazard' => false],
            ['fkko_code' => 'код фкко · 9 21 000', 'glyph' => 'А', 'title_html' => 'Аккумуляторы и <em>батареи</em>',      'description' => 'Свинцово-кислотные, Li-ion, литиевые, щелочные.',              'hazard_class_label' => 'II–III',      'is_hazard' => true],
            ['fkko_code' => 'код фкко · — / прочее', 'glyph' => '⟡', 'title_html' => 'И другие <em>категории</em>',         'description' => 'Полный перечень предоставим по запросу: 150+ кодов ФККО.',     'hazard_class_label' => 'по запросу',  'is_hazard' => false],
        ];

        foreach ($cards as $i => $row) {
            WasteType::updateOrCreate(
                ['sort' => ($i + 1) * 10],
                array_merge($row, ['is_active' => true])
            );
        }
    }
}
