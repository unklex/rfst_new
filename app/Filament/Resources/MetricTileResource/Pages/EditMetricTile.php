<?php

namespace App\Filament\Resources\MetricTileResource\Pages;

use App\Filament\Resources\MetricTileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMetricTile extends EditRecord
{
    protected static string $resource = MetricTileResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
