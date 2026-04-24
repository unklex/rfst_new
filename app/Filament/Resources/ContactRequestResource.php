<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ContactRequestStatus;
use App\Filament\Resources\ContactRequestResource\Pages;
use App\Jobs\ForwardLeadToFastApiJob;
use App\Models\ContactRequest;
use App\Settings\ContactSettings;
use App\Settings\IntegrationSettings;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ContactRequestResource extends Resource
{
    protected static ?string $model = ContactRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationGroup = 'Заявки';
    protected static ?string $navigationLabel = 'Обращения';
    protected static ?string $modelLabel = 'Заявка';
    protected static ?string $pluralModelLabel = 'Заявки';
    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->where('status', ContactRequestStatus::New->value)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Статус')
                ->description('Флипните статус на «Обработана», когда с клиентом связались.')
                ->schema([
                    Select::make('status')
                        ->label('Статус')
                        ->options(collect(ContactRequestStatus::cases())
                            ->mapWithKeys(fn (ContactRequestStatus $c) => [$c->value => $c->getLabel()])
                            ->all())
                        ->required()
                        ->native(false),
                    DateTimePicker::make('handled_at')
                        ->label('Обработана в')
                        ->seconds(false)
                        ->displayFormat('d.m.Y H:i'),
                ])->columns(2),

            Section::make('Контакт')
                ->schema([
                    TextInput::make('name')->label('Имя')->disabled()->dehydrated(false),
                    TextInput::make('phone')->label('Телефон')->disabled()->dehydrated(false),
                    TextInput::make('email')->label('E-mail')->disabled()->dehydrated(false),
                    Textarea::make('message')->label('Сообщение')->disabled()->dehydrated(false)->columnSpanFull()->rows(4),
                ])->columns(2),

            Section::make('Метаданные')
                ->schema([
                    Placeholder::make('created_at')->label('Получена')
                        ->content(fn (?ContactRequest $record) => $record?->created_at?->format('d.m.Y H:i:s')),
                    Placeholder::make('ip_hash')->label('IP (SHA-256)')
                        ->content(fn (?ContactRequest $record) => $record?->ip_hash ?? '—'),
                    Placeholder::make('user_agent')->label('User-Agent')
                        ->content(fn (?ContactRequest $record) => $record?->user_agent ?? '—')
                        ->columnSpanFull(),
                    Placeholder::make('referer_url')->label('Referer')
                        ->content(fn (?ContactRequest $record) => $record?->referer_url ?? '—'),
                    Placeholder::make('landing_url')->label('Landing URL')
                        ->content(fn (?ContactRequest $record) => $record?->landing_url ?? '—'),
                    Placeholder::make('consent_text_hash')->label('Согласие (SHA-256)')
                        ->content(function (?ContactRequest $record): string {
                            if ($record === null || $record->consent_text_hash === null) {
                                return '—';
                            }
                            $current = hash('sha256', (string) app(ContactSettings::class)->personal_data_consent_text);
                            $tag = $record->consent_text_hash === $current ? 'актуально' : 'устарело';

                            return substr($record->consent_text_hash, 0, 12) . ' · ' . $tag;
                        }),
                ])->columns(2)->collapsed(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Контакт')->schema([
                TextEntry::make('name')->label('Имя'),
                TextEntry::make('phone')->label('Телефон')->copyable(),
                TextEntry::make('email')->label('E-mail')->copyable()->placeholder('—'),
                TextEntry::make('status')->label('Статус')->badge(),
                TextEntry::make('message')->label('Сообщение')->columnSpanFull()->placeholder('—'),
            ])->columns(2),

            InfoSection::make('Согласие на обработку ПД (152-ФЗ)')->schema([
                TextEntry::make('consent_accepted')->label('Согласие')
                    ->formatStateUsing(fn ($state): string => $state ? 'Да' : 'Нет'),
                TextEntry::make('consent_text_hash')
                    ->label('Версия текста (SHA-256)')
                    ->copyable()
                    ->badge()
                    ->color(function (ContactRequest $r): string {
                        $current = hash('sha256', (string) app(ContactSettings::class)->personal_data_consent_text);

                        return $r->consent_text_hash === $current ? 'success' : 'gray';
                    })
                    ->formatStateUsing(function (?string $state): string {
                        if ($state === null) {
                            return '—';
                        }
                        $current = hash('sha256', (string) app(ContactSettings::class)->personal_data_consent_text);

                        return substr($state, 0, 12) . ' · ' . ($state === $current ? 'актуально' : 'устарело');
                    }),
            ])->columns(2),

            InfoSection::make('FastAPI / CRM')->schema([
                TextEntry::make('fastapi_status_code')
                    ->label('HTTP статус')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 200 && $state < 300 => 'success',
                        default => 'danger',
                    })
                    ->placeholder('—'),
                TextEntry::make('external_id')->label('Внешний ID')->copyable()->placeholder('—'),
                TextEntry::make('forwarded_at')->label('Отправлена')->dateTime('d.m.Y H:i:s')->placeholder('—'),
                KeyValueEntry::make('fastapi_response')->label('Ответ FastAPI')->columnSpanFull(),
            ])->columns(3)->collapsed(),

            InfoSection::make('Трекинг')->schema([
                KeyValueEntry::make('utm')->label('UTM')->columnSpanFull(),
                TextEntry::make('referer_url')->label('Referer')->copyable()->placeholder('—'),
                TextEntry::make('landing_url')->label('Landing URL')->copyable()->placeholder('—'),
                TextEntry::make('ip_hash')->label('IP (SHA-256)')->copyable()->placeholder('—'),
                TextEntry::make('user_agent')->label('User-Agent')->columnSpanFull()->placeholder('—'),
                TextEntry::make('created_at')->label('Получена')->dateTime('d.m.Y H:i:s'),
                TextEntry::make('handled_at')->label('Обработана')->dateTime('d.m.Y H:i:s')->placeholder('—'),
            ])->columns(2)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Получена')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('name')->label('Имя')->searchable(),
                TextColumn::make('phone')->label('Телефон')->searchable()->copyable(),
                TextColumn::make('email')->label('E-mail')->searchable()->toggleable()->placeholder('—'),
                TextColumn::make('status')->label('Статус')->badge()->sortable(),
                TextColumn::make('utm_source')
                    ->label('UTM source')
                    ->getStateUsing(fn (ContactRequest $r): ?string => $r->utm['utm_source'] ?? null)
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('utm_campaign')
                    ->label('UTM campaign')
                    ->getStateUsing(fn (ContactRequest $r): ?string => $r->utm['utm_campaign'] ?? null)
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('fastapi_status_code')
                    ->label('FastAPI')
                    ->toggleable()
                    ->placeholder('—')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 200 && $state < 300 => 'success',
                        default => 'danger',
                    }),
                TextColumn::make('external_id')
                    ->label('Внешний ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),
                TextColumn::make('forwarded_at')
                    ->label('Отправлена')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                TextColumn::make('handled_at')->label('Обработана')->dateTime('d.m.Y H:i')->toggleable()->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(collect(ContactRequestStatus::cases())
                        ->mapWithKeys(fn (ContactRequestStatus $c) => [$c->value => $c->getLabel()])
                        ->all()),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('resend_fastapi')
                    ->label('Отправить в FastAPI')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (ContactRequest $r): bool => in_array($r->status, [
                        ContactRequestStatus::New,
                        ContactRequestStatus::Failed,
                    ], true) && !empty(app(IntegrationSettings::class)->fastapi_lead_url))
                    ->action(function (ContactRequest $r): void {
                        dispatch(new ForwardLeadToFastApiJob($r->id))->afterResponse();
                        Notification::make()
                            ->title('Заявка отправлена в очередь')
                            ->body('Результат появится через несколько секунд — обновите список.')
                            ->success()
                            ->send();
                    }),
                Action::make('mark_handled')
                    ->label('Отметить обработанной')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (ContactRequest $r): bool => $r->status !== ContactRequestStatus::Handled)
                    ->action(function (ContactRequest $r): void {
                        $r->update([
                            'status' => ContactRequestStatus::Handled,
                            'handled_at' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([
                BulkAction::make('mark_handled')
                    ->label('Отметить обработанными')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records): void {
                        foreach ($records as $record) {
                            $record->update([
                                'status' => ContactRequestStatus::Handled,
                                'handled_at' => now(),
                            ]);
                        }
                    }),
            ]);
    }

    public static function canCreate(): bool
    {
        // Leads come from the public form only.
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactRequests::route('/'),
            'view' => Pages\ViewContactRequest::route('/{record}'),
            'edit' => Pages\EditContactRequest::route('/{record}/edit'),
        ];
    }
}
