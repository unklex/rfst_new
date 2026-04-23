<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactRequestResource\Pages;

use App\Enums\ContactRequestStatus;
use App\Filament\Resources\ContactRequestResource;
use App\Models\ContactRequest;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContactRequest extends ViewRecord
{
    protected static string $resource = ContactRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('mark_handled')
                ->label('Отметить обработанной')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (ContactRequest $record): bool => $record->status !== ContactRequestStatus::Handled)
                ->action(function (ContactRequest $record): void {
                    $record->update([
                        'status' => ContactRequestStatus::Handled,
                        'handled_at' => now(),
                    ]);
                }),
        ];
    }
}
