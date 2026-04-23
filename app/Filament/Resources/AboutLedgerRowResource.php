<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AboutLedgerRowResource\Pages;
use App\Models\AboutLedgerRow;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AboutLedgerRowResource extends Resource
{
    protected static ?string $model = AboutLedgerRow::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'О компании — строки таблицы';
    protected static ?string $modelLabel = 'Строка «О компании»';
    protected static ?string $pluralModelLabel = 'Строки «О компании»';
    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make()->schema([
                TextInput::make('code')->label('Код / префикс (01 · Лицензия)')->required()->maxLength(80),
                RichEditor::make('title_html')->label('Центр — заголовок строки (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                RichEditor::make('detail_html')->label('Справа — деталь (HTML с <br>)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                TextInput::make('sort')->label('Порядок')->numeric()->default(10)->required(),
                Toggle::make('is_active')->label('Показывать')->default(true)->inline(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('sort')->label('#')->sortable(),
                TextColumn::make('code')->label('Код')->searchable(),
                TextColumn::make('title_html')->label('Заголовок')->html()->wrap(),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Активные')])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAboutLedgerRows::route('/'),
            'create' => Pages\CreateAboutLedgerRow::route('/create'),
            'edit' => Pages\EditAboutLedgerRow::route('/{record}/edit'),
        ];
    }
}
