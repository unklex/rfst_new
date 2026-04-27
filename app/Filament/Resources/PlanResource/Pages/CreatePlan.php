<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Resources\PlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = array_values(array_map(
                fn ($i) => is_array($i) ? (string) ($i['text'] ?? '') : (string) $i,
                $data['features']
            ));
        }
        return $data;
    }
}
