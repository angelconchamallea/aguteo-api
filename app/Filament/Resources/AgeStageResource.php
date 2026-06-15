<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgeStageResource\Pages;
use App\Models\AgeStage;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AgeStageResource extends Resource
{
    protected static ?string $model = AgeStage::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?int $navigationSort = 40;
    protected static ?string $modelLabel = 'Etapa';
    protected static ?string $pluralModelLabel = 'Etapas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(60)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(AgeStage::class, 'slug', ignoreRecord: true),
                ]),
                TextInput::make('tagline')
                    ->label('Tagline')
                    ->maxLength(120)
                    ->columnSpanFull(),
                Grid::make(3)->schema([
                    TextInput::make('min_months')
                        ->label('Desde (meses)')
                        ->numeric()
                        ->required()
                        ->minValue(0),
                    TextInput::make('max_months')
                        ->label('Hasta (meses)')
                        ->numeric()
                        ->required()
                        ->minValue(1),
                    TextInput::make('sort_order')
                        ->label('Orden')
                        ->numeric()
                        ->default(0),
                ]),
                ColorPicker::make('color_token')
                    ->label('Color')
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Etapa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tagline')
                    ->label('Tagline')
                    ->limit(50),
                TextColumn::make('min_months')
                    ->label('Desde (m)')
                    ->sortable(),
                TextColumn::make('max_months')
                    ->label('Hasta (m)')
                    ->sortable(),
                TextColumn::make('color_token')
                    ->label('Color')
                    ->html()
                    ->formatStateUsing(fn(?string $state) => $state
                        ? "<span style='display:inline-flex;align-items:center;gap:6px;'><span style='width:16px;height:16px;border-radius:3px;background:{$state};border:1px solid rgba(0,0,0,.15);flex-shrink:0;'></span>{$state}</span>"
                        : '<span style="color:#9ca3af">—</span>'),
                TextColumn::make('products_count')
                    ->label('Productos')
                    ->counts('products'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAgeStages::route('/'),
            'create' => Pages\CreateAgeStage::route('/create'),
            'edit'   => Pages\EditAgeStage::route('/{record}/edit'),
        ];
    }
}
