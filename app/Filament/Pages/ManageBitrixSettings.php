<?php

namespace App\Filament\Pages;

use App\Settings\BitrixSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageBitrixSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = BitrixSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Битрикс24 (§ 03)';
    protected static ?int $navigationSort = 90;
    protected static ?string $title = 'Битрикс24 — § 03';

    public function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Левая колонка — интро')
                ->description('Элементы списка (A.01-A.04) редактируются в «Справочники → Возможности Битрикс24».')
                ->schema([
                    TextInput::make('kicker')->label('Верхняя подпись (§ 03 · ...)')->required()->maxLength(120),
                    RichEditor::make('heading_html')->label('Заголовок (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                    Textarea::make('paragraph')->label('Абзац под заголовком')->rows(4)->required(),
                    TextInput::make('cta_label')->label('Кнопка — текст')->required()->maxLength(80),
                    TextInput::make('cta_url')->label('Кнопка — URL')->required()->maxLength(300),
                ])->columns(2),

            Section::make('Правая колонка — мок окна Битрикс24')
                ->description('Колонки и карточки — в «Справочники → Колонки демо-канбан» и её связях.')
                ->schema([
                    TextInput::make('mock_url')->label('URL в адресной строке мока')->required()->maxLength(200),
                    TextInput::make('mock_version')->label('Номер версии справа')->required()->maxLength(40),
                    TextInput::make('mock_footer_left')->label('Футер мока — слева')->required()->maxLength(120),
                    TextInput::make('mock_footer_right_html')->label('Футер мока — справа (HTML)')->required()->maxLength(160),
                    TextInput::make('caption')->label('Подпись под окном (fig.02 ...)')->required()->maxLength(200),
                ])->columns(2),
        ]);
    }

}
