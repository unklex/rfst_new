<?php

namespace App\Filament\Pages;

use App\Settings\HeroSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageHeroSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = HeroSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Hero (первый экран)';
    protected static ?int $navigationSort = 50;
    protected static ?string $title = 'Hero — первый экран';

    public function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Код документа и знак опасности')->schema([
                RichEditor::make('ref_code_html')->label('Слаг-код (ref / дата)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                TextInput::make('hazard_label')->label('Текст в ромбе опасности')->required()->maxLength(16),
            ])->columns(1),

            Section::make('Заголовок и лид')->schema([
                RichEditor::make('headline_html')->label('H1 — главный заголовок (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull()->helperText('Допустимы <em>, <br>'),
                RichEditor::make('lede_html')->label('Лид-абзац под заголовком')->toolbarButtons($toolbar)->required()->columnSpanFull(),
            ])->columns(1),

            Section::make('Кнопки CTA')->schema([
                TextInput::make('cta_primary_label')->label('Основная — текст')->required()->maxLength(120),
                TextInput::make('cta_primary_anchor')->label('Основная — якорь/URL')->required()->maxLength(200),
                TextInput::make('cta_secondary_label')->label('Вторая — текст')->required()->maxLength(120),
                TextInput::make('cta_secondary_anchor')->label('Вторая — якорь/URL')->required()->maxLength(200),
            ])->columns(2),

            Section::make('Подпись директора')->schema([
                TextInput::make('signature_name')->label('Имя')->required()->maxLength(80),
                Textarea::make('signature_caption_html')->label('Подпись (HTML)')->rows(2)->required(),
            ])->columns(1),

            Section::make('Карточка A — статистика «10+ лет»')->schema([
                TextInput::make('card_a_kicker')->label('Строка сверху (§ 001 · ...)')->required()->maxLength(120),
                TextInput::make('card_a_big_value')->label('Большое число')->required()->maxLength(10),
                TextInput::make('card_a_big_suffix')->label('Суффикс (+)')->maxLength(6),
                TextInput::make('card_a_label_strong')->label('Подпись — жирная')->required()->maxLength(30),
                Textarea::make('card_a_label_text')->label('Подпись — текст')->rows(2)->required(),
                TextInput::make('card_a_stat1_value')->label('Показатель 1 — число')->required()->maxLength(16),
                TextInput::make('card_a_stat1_label')->label('Показатель 1 — подпись')->required()->maxLength(40),
                TextInput::make('card_a_stat2_value')->label('Показатель 2 — число')->required()->maxLength(16),
                TextInput::make('card_a_stat2_label')->label('Показатель 2 — подпись')->required()->maxLength(40),
                TextInput::make('card_a_stat3_value')->label('Показатель 3 — число')->required()->maxLength(16),
                TextInput::make('card_a_stat3_label')->label('Показатель 3 — подпись')->required()->maxLength(40),
            ])->columns(2),

            Section::make('Карточка B — лицензия (тёмная)')->schema([
                TextInput::make('card_b_kicker')->label('Строка сверху')->required()->maxLength(120),
                RichEditor::make('card_b_title_html')->label('Заголовок карточки (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                TextInput::make('card_b_license_number')->label('Номер лицензии (жирно)')->required()->maxLength(40),
                TextInput::make('card_b_license_detail')->label('Детали (дата и т. д.)')->required()->maxLength(120),
                TextInput::make('card_b_class_label')->label('Подпись «Класс»')->required()->maxLength(40),
                TextInput::make('card_b_class_value')->label('Значение класса')->required()->maxLength(80),
            ])->columns(2),
        ]);
    }

}
