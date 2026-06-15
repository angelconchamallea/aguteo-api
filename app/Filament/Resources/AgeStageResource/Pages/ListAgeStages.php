<?php

namespace App\Filament\Resources\AgeStageResource\Pages;

use App\Filament\Resources\AgeStageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAgeStages extends ListRecords
{
    protected static string $resource = AgeStageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Nueva etapa')];
    }
}
