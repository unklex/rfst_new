<?php

namespace App\Filament\Resources\BitrixFeatureResource\Pages;

use App\Filament\Resources\BitrixFeatureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBitrixFeature extends EditRecord
{
    protected static string $resource = BitrixFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
