<?php

namespace App\Filament\Resources\AboutLedgerRowResource\Pages;

use App\Filament\Resources\AboutLedgerRowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAboutLedgerRows extends ListRecords
{
    protected static string $resource = AboutLedgerRowResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
