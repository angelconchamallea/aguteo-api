<?php

namespace App\Filament\Resources\AgeStageResource\Pages;

use App\Filament\Resources\AgeStageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAgeStage extends CreateRecord
{
    protected static string $resource = AgeStageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
