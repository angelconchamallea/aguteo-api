<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?int $navigationSort = 10;
    protected static ?string $modelLabel = 'Pedido';
    protected static ?string $pluralModelLabel = 'Pedidos';

    public static function getNavigationBadge(): ?string
    {
        return (string) Order::where('status', 'paid')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Cliente y entrega')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('order_number')->label('N° Pedido'),
                    TextEntry::make('customer.name')->label('Cliente'),
                    TextEntry::make('customer.email')->label('Email'),
                ]),
                TextEntry::make('shipping_address')
                    ->label('Dirección de envío')
                    ->formatStateUsing(function (?array $state) {
                        if (! $state) return '—';
                        return implode(', ', array_filter([
                            $state['street'] ?? null,
                            $state['commune'] ?? null,
                            $state['region'] ?? null,
                        ]));
                    }),
                TextEntry::make('shipping_rate_name')->label('Modalidad de envío'),
            ]),

            InfoSection::make('Items del pedido')->schema([
                RepeatableEntry::make('items')->label('')->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('product_name')->label('Producto'),
                        TextEntry::make('variant_label')->label('Variante')->placeholder('—'),
                        TextEntry::make('quantity')->label('Cant.'),
                        TextEntry::make('unit_price')
                            ->label('Precio unit.')
                            ->formatStateUsing(fn(?int $state) => '$' . number_format($state ?? 0, 0, ',', '.')),
                    ]),
                ]),
            ]),

            InfoSection::make('Totales')->schema([
                Grid::make(4)->schema([
                    TextEntry::make('subtotal')
                        ->label('Subtotal')
                        ->formatStateUsing(fn(?int $state) => '$' . number_format($state ?? 0, 0, ',', '.')),
                    TextEntry::make('discount_total')
                        ->label('Descuento')
                        ->formatStateUsing(fn(?int $state) => '-$' . number_format($state ?? 0, 0, ',', '.')),
                    TextEntry::make('shipping_total')
                        ->label('Envío')
                        ->formatStateUsing(fn(?int $state) => '$' . number_format($state ?? 0, 0, ',', '.')),
                    TextEntry::make('total')
                        ->label('TOTAL')
                        ->formatStateUsing(fn(?int $state) => '$' . number_format($state ?? 0, 0, ',', '.'))
                        ->weight('bold'),
                ]),
            ]),

            InfoSection::make('Datos Webpay')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('webpay_authorization_code')->label('Código autorización')->placeholder('—'),
                    TextEntry::make('webpay_card_last4')->label('Últimos 4 dígitos tarjeta')->placeholder('—'),
                    TextEntry::make('payment_method')->label('Método de pago'),
                ]),
            ]),

            InfoSection::make('Timeline')->schema([
                Grid::make(4)->schema([
                    TextEntry::make('created_at')->label('Creado')->dateTime('d/m/Y H:i'),
                    TextEntry::make('paid_at')->label('Pagado')->dateTime('d/m/Y H:i')->placeholder('—'),
                    TextEntry::make('shipped_at')->label('Despachado')->dateTime('d/m/Y H:i')->placeholder('—'),
                    TextEntry::make('delivered_at')->label('Entregado')->dateTime('d/m/Y H:i')->placeholder('—'),
                ]),
                TextEntry::make('notes')->label('Notas')->placeholder('Sin notas')->columnSpanFull(),
            ]),
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Estado y seguimiento')->schema([
                Select::make('status')
                    ->label('Estado')
                    ->options(function (Order $record) {
                        // Solo transiciones válidas según estado actual
                        $transitions = [
                            'pending'    => ['pending' => 'Pendiente', 'cancelled' => 'Cancelado'],
                            'paid'       => ['paid' => 'Pagado', 'preparing' => 'Preparando', 'cancelled' => 'Cancelado'],
                            'preparing'  => ['preparing' => 'Preparando', 'shipped' => 'Enviado', 'cancelled' => 'Cancelado'],
                            'shipped'    => ['shipped' => 'Enviado', 'delivered' => 'Entregado'],
                            'delivered'  => ['delivered' => 'Entregado'],
                            'cancelled'  => ['cancelled' => 'Cancelado'],
                            'failed'     => ['failed' => 'Fallido', 'cancelled' => 'Cancelado'],
                        ];
                        return $transitions[$record->status] ?? [];
                    })
                    ->required(),
                Textarea::make('notes')
                    ->label('Notas internas')
                    ->rows(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('N° Pedido')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'pending'   => 'Pendiente',
                        'paid'      => 'Pagado',
                        'preparing' => 'Preparando',
                        'shipped'   => 'Enviado',
                        'delivered' => 'Entregado',
                        'cancelled' => 'Cancelado',
                        'failed'    => 'Fallido',
                        default     => $state,
                    })
                    ->color(fn(string $state) => match ($state) {
                        'pending'   => 'gray',
                        'paid'      => 'success',
                        'preparing' => 'info',
                        'shipped'   => 'warning',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        'failed'    => 'danger',
                        default     => 'gray',
                    }),
                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn(?int $state) => '$' . number_format($state ?? 0, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
                TextColumn::make('paid_at')
                    ->label('Pagado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending'   => 'Pendiente',
                        'paid'      => 'Pagado',
                        'preparing' => 'Preparando',
                        'shipped'   => 'Enviado',
                        'delivered' => 'Entregado',
                        'cancelled' => 'Cancelado',
                        'failed'    => 'Fallido',
                    ]),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('from')->label('Desde'),
                        DateTimePicker::make('to')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $v) => $q->where('created_at', '>=', $v))
                            ->when($data['to'], fn($q, $v) => $q->where('created_at', '<=', $v));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->label('Estado/Notas'),
                Tables\Actions\Action::make('mark_shipped')
                    ->label('Marcar enviado')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->visible(fn(Order $record) => $record->status === 'preparing')
                    ->requiresConfirmation()
                    ->form([
                        TextInput::make('tracking')
                            ->label('Número de seguimiento (opcional)')
                            ->nullable(),
                    ])
                    ->action(function (Order $record, array $data) {
                        $record->markAsShipped($data['tracking'] ?? null);
                    }),
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
            'index'  => Pages\ListOrders::route('/'),
            'view'   => Pages\ViewOrder::route('/{record}'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
