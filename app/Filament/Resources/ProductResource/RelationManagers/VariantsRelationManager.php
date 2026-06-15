<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';
    protected static ?string $title = 'Variantes';

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                TextInput::make('size')
                    ->label('Talla')
                    ->maxLength(30)
                    ->nullable(),
                ColorPicker::make('color')
                    ->label('Color')
                    ->nullable(),
            ]),
            TextInput::make('sku')
                ->label('SKU variante')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true),
            Grid::make(2)->schema([
                TextInput::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('price_override')
                    ->label('Precio especial ($)')
                    ->numeric()
                    ->prefix('$')
                    ->nullable()
                    ->helperText('Dejar vacío para usar el precio base del producto'),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('size')
                    ->label('Talla'),
                TextColumn::make('color')
                    ->label('Color')
                    ->html()
                    ->formatStateUsing(fn(?string $state) => $state
                        ? "<span style='display:inline-flex;align-items:center;gap:6px;'><span style='width:14px;height:14px;border-radius:3px;background:{$state};border:1px solid rgba(0,0,0,.15);flex-shrink:0;'></span>{$state}</span>"
                        : '<span style="color:#9ca3af">—</span>'),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->copyable(),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->badge()
                    ->color(fn(int $state) => match (true) {
                        $state === 0 => 'danger',
                        $state <= 3  => 'warning',
                        default      => 'success',
                    }),
                TextColumn::make('price_override')
                    ->label('Precio especial')
                    ->formatStateUsing(fn(?int $state) => $state ? '$' . number_format($state, 0, ',', '.') : '—'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Agregar variante'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('¿Eliminar esta variante? El stock asociado se perderá.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
