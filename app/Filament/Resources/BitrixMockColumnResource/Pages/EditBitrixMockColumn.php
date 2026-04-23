<?php

namespace App\Filament\Resources\BitrixMockColumnResource\Pages;

use App\Filament\Resources\BitrixMockColumnResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBitrixMockColumn extends EditRecord
{
    protected static string $resource = BitrixMockColumnResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
