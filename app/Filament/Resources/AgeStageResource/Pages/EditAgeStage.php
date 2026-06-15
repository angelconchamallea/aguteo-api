<?php

namespace App\Filament\Resources\AgeStageResource\Pages;

use App\Filament\Resources\AgeStageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgeStage extends EditRecord
{
    protected static string $resource = AgeStageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()->requiresConfirmation()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
