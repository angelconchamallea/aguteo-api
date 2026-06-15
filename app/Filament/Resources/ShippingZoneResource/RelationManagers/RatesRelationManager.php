<?php

namespace App\Filament\Resources\ShippingZoneResource\RelationManagers;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RatesRelationManager extends RelationManager
{
    protected static string $relationship = 'rates';
    protected static ?string $title = 'Tarifas';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nombre de la tarifa')
                ->required()
                ->maxLength(100)
                ->placeholder('Ej: Envío estándar RM'),
            Grid::make(3)->schema([
                TextInput::make('price')
                    ->label('Precio ($)')
                    ->numeric()
                    ->required()
                    ->prefix('$')
                    ->minValue(0),
                TextInput::make('free_from_amount')
                    ->label('Gratis desde ($)')
                    ->numeric()
                    ->prefix('$')
                    ->nullable()
                    ->helperText('Dejar vacío si no hay envío gratis'),
                TextInput::make('estimated_days')
                    ->label('Días estimados')
                    ->numeric()
                    ->nullable(),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tarifa'),
                TextColumn::make('price')
                    ->label('Precio')
                    ->formatStateUsing(fn(?int $state) => '$' . number_format($state ?? 0, 0, ',', '.')),
                TextColumn::make('free_from_amount')
                    ->label('Gratis desde')
                    ->formatStateUsing(fn(?int $state) => $state ? '$' . number_format($state, 0, ',', '.') : '—'),
                TextColumn::make('estimated_days')
                    ->label('Días est.')
                    ->placeholder('—'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Agregar tarifa'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('¿Eliminar esta tarifa de envío?'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
