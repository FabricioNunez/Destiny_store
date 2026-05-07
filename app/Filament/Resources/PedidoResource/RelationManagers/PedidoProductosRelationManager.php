<?php

namespace App\Filament\Resources\PedidoResource\RelationManagers;

use App\Models\Producto;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Support\Facades\DB;

class PedidoProductosRelationManager extends RelationManager
{
    protected static string $relationship = 'productos';

    protected static ?string $title = 'Detalle de productos';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->label('Producto')->sortable()->searchable(),

                Tables\Columns\TextColumn::make('pivot.cantidad')->label('Cantidad')->sortable(),

                Tables\Columns\TextColumn::make('precio')->label('Precio unitario')->money('ARS')->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('ARS')
                    ->getStateUsing(fn ($record) => $record->precio * $record->pivot->cantidad),
            ])
            ->headerActions([
                Tables\Actions\Action::make('agregar_producto')
                    ->label('Agregar producto')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Select::make('producto_id')
                            ->label('Producto')
                            ->options(Producto::query()->pluck('nombre', 'id'))
                            ->searchable()
                            ->required(),

                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $pedido = $this->ownerRecord;

                        DB::transaction(function () use ($pedido, $data) {
                            $producto = Producto::findOrFail($data['producto_id']);
                            $cantidad = (int) $data['cantidad'];

                            if ($cantidad > $producto->stock) {
                                throw new \Exception('Stock insuficiente para ' . $producto->nombre);
                            }

                            $productoYaAgregado = $pedido->productos()
                                ->where('productos.id', $producto->id)
                                ->first();

                            if ($productoYaAgregado) {
                                $cantidadActual = (int) $productoYaAgregado->pivot->cantidad;

                                $pedido->productos()->updateExistingPivot($producto->id, [
                                    'cantidad' => $cantidadActual + $cantidad,
                                ]);
                            } else {
                                $pedido->productos()->attach($producto->id, [
                                    'cantidad' => $cantidad,
                                ]);
                            }

                            $producto->stock -= $cantidad;
                            $producto->save();

                            $this->recalcularTotal($pedido);
                        });

                        Notification::make()
                            ->title('Producto agregado')
                            ->success()
                            ->send();
                    })
                    ->failureNotificationTitle('No se pudo agregar el producto'),
            ])
            ->actions([
                Tables\Actions\Action::make('editar_producto')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Select::make('producto_id')
                            ->label('Producto')
                            ->options(Producto::query()->pluck('nombre', 'id'))
                            ->searchable()
                            ->required(),

                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->fillForm(fn ($record): array => [
                        'producto_id' => $record->id,
                        'cantidad' => $record->pivot->cantidad,
                    ])
                    ->action(function ($record, array $data): void {
                        $pedido = $this->ownerRecord;

                        DB::transaction(function () use ($pedido, $record, $data) {
                            $productoAnterior = Producto::findOrFail($record->id);
                            $cantidadAnterior = (int) $record->pivot->cantidad;

                            $productoNuevo = Producto::findOrFail($data['producto_id']);
                            $cantidadNueva = (int) $data['cantidad'];

                            if ($productoAnterior->id === $productoNuevo->id) {
                                $stockDisponibleReal = $productoNuevo->stock + $cantidadAnterior;

                                if ($cantidadNueva > $stockDisponibleReal) {
                                    throw new \Exception('Stock insuficiente para ' . $productoNuevo->nombre);
                                }

                                $productoNuevo->stock = $stockDisponibleReal - $cantidadNueva;
                                $productoNuevo->save();

                                $pedido->productos()->updateExistingPivot($productoNuevo->id, [
                                    'cantidad' => $cantidadNueva,
                                ]);
                            } else {
                                $productoAnterior->stock += $cantidadAnterior;
                                $productoAnterior->save();

                                if ($cantidadNueva > $productoNuevo->stock) {
                                    throw new \Exception('Stock insuficiente para ' . $productoNuevo->nombre);
                                }

                                $productoNuevo->stock -= $cantidadNueva;
                                $productoNuevo->save();

                                $pedido->productos()->detach($productoAnterior->id);

                                $productoExistente = $pedido->productos()
                                    ->where('productos.id', $productoNuevo->id)
                                    ->first();

                                if ($productoExistente) {
                                    $pedido->productos()->updateExistingPivot($productoNuevo->id, [
                                        'cantidad' => $productoExistente->pivot->cantidad + $cantidadNueva,
                                    ]);
                                } else {
                                    $pedido->productos()->attach($productoNuevo->id, [
                                        'cantidad' => $cantidadNueva,
                                    ]);
                                }
                            }

                            $this->recalcularTotal($pedido);
                        });

                        Notification::make()
                            ->title('Producto actualizado')
                            ->success()
                            ->send();
                    })
                    ->failureNotificationTitle('No se pudo actualizar el producto'),

                Tables\Actions\Action::make('eliminar_producto')
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $pedido = $this->ownerRecord;

                        DB::transaction(function () use ($pedido, $record) {
                            $producto = Producto::findOrFail($record->id);
                            $cantidad = (int) $record->pivot->cantidad;

                            $producto->stock += $cantidad;
                            $producto->save();

                            $pedido->productos()->detach($producto->id);

                            $this->recalcularTotal($pedido);
                        });

                        Notification::make()
                            ->title('Producto eliminado')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    private function recalcularTotal($pedido): void
    {
        $pedido->load('productos');

        $total = $pedido->productos->sum(function ($producto) {
            return $producto->precio * $producto->pivot->cantidad;
        });

        $pedido->update([
            'total' => $total,
        ]);
    }
}