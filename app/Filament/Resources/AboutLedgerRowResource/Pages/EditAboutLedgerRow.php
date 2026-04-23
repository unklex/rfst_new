<?php

namespace App\Filament\Resources\AboutLedgerRowResource\Pages;

use App\Filament\Resources\AboutLedgerRowResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAboutLedgerRow extends EditRecord
{
    protected static string $resource = AboutLedgerRowResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
