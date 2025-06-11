<?php

namespace App\Filament\Resources\PedidoResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class PedidoProductosRelationManager extends RelationManager
{
    protected static string $relationship = 'productos';
    protected static ?string $title = 'Detalle de productos';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Producto')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('pivot.cantidad')
                    ->label('Cantidad')
                    ->sortable(),

                Tables\Columns\TextColumn::make('precio')
                    ->label('Precio unitario')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('ARS')
                    ->getStateUsing(fn ($record) => $record->precio * $record->pivot->cantidad)
                    ->sortable(),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
