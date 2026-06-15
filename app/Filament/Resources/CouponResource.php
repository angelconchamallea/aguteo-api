<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?int $navigationSort = 20;
    protected static ?string $modelLabel = 'Cupón';
    protected static ?string $pluralModelLabel = 'Cupones';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Grid::make(2)->schema([
                    TextInput::make('code')
                        ->label('Código')
                        ->required()
                        ->maxLength(50)
                        ->unique(Coupon::class, 'code', ignoreRecord: true)
                        ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                        ->dehydrateStateUsing(fn(string $state) => strtoupper($state)),
                    Select::make('type')
                        ->label('Tipo')
                        ->options(['percent' => 'Porcentaje (%)', 'fixed' => 'Monto fijo ($)'])
                        ->required()
                        ->live(),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('value')
                        ->label(fn(Get $get) => $get('type') === 'percent' ? 'Descuento (%)' : 'Descuento ($)')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->suffix(fn(Get $get) => $get('type') === 'percent' ? '%' : null)
                        ->prefix(fn(Get $get) => $get('type') === 'fixed' ? '$' : null),
                    TextInput::make('min_purchase_amount')
                        ->label('Compra mínima ($)')
                        ->numeric()
                        ->prefix('$')
                        ->nullable()
                        ->helperText('Dejar vacío si no hay mínimo'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('usage_limit')
                        ->label('Usos totales máximos')
                        ->numeric()
                        ->nullable()
                        ->helperText('Dejar vacío para usos ilimitados'),
                    TextInput::make('usage_limit_per_customer')
                        ->label('Usos por cliente')
                        ->numeric()
                        ->default(1),
                ]),
                Grid::make(2)->schema([
                    DateTimePicker::make('starts_at')
                        ->label('Válido desde')
                        ->nullable(),
                    DateTimePicker::make('expires_at')
                        ->label('Válido hasta')
                        ->nullable()
                        ->after('starts_at'),
                ]),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => $state === 'percent' ? 'Porcentaje' : 'Monto fijo')
                    ->color(fn(string $state) => $state === 'percent' ? 'info' : 'warning'),
                TextColumn::make('value')
                    ->label('Descuento')
                    ->formatStateUsing(function (Coupon $record): string {
                        return $record->type === 'percent'
                            ? $record->value . '%'
                            : '$' . number_format($record->value, 0, ',', '.');
                    }),
                TextColumn::make('usage')
                    ->label('Usos')
                    ->getStateUsing(fn(Coupon $record) => $record->times_used . ($record->usage_limit ? '/' . $record->usage_limit : '')),
                TextColumn::make('expires_at')
                    ->label('Vence')
                    ->date('d/m/Y')
                    ->color(fn(?string $state) => $state && now()->gt($state) ? 'danger' : null)
                    ->placeholder('Sin vencimiento'),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(['percent' => 'Porcentaje', 'fixed' => 'Monto fijo']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('¿Desactivar este cupón? Los clientes no podrán usarlo hasta que lo reactives.')
                    ->action(fn(Coupon $record) => $record->update(['is_active' => false]))
                    ->visible(fn(Coupon $record) => $record->is_active),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
