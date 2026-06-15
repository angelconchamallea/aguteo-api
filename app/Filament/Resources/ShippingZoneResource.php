<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingZoneResource\Pages;
use App\Filament\Resources\ShippingZoneResource\RelationManagers;
use App\Models\ShippingZone;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingZoneResource extends Resource
{
    protected static ?string $model = ShippingZone::class;
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int $navigationSort = 10;
    protected static ?string $modelLabel = 'Zona de envío';
    protected static ?string $pluralModelLabel = 'Zonas de envío';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(100),
                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true),
                Select::make('communes')
                    ->label('Comunas cubiertas')
                    ->relationship('communes', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull()
                    ->helperText('Selecciona las comunas que pertenecen a esta zona'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Zona')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('communes_count')
                    ->label('Comunas')
                    ->counts('communes'),
                TextColumn::make('rates_count')
                    ->label('Tarifas')
                    ->counts('rates'),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Activa'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('¿Eliminar esta zona? Las tarifas asociadas también se eliminarán.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListShippingZones::route('/'),
            'create' => Pages\CreateShippingZone::route('/create'),
            'edit'   => Pages\EditShippingZone::route('/{record}/edit'),
        ];
    }
}
