<?php

namespace App\Filament\Resources\MetricTileResource\Pages;

use App\Filament\Resources\MetricTileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMetricTiles extends ListRecords
{
    protected static string $resource = MetricTileResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
