<?php

namespace App\Filament\Resources\PedidoResource\Pages;

use App\Filament\Resources\PedidoResource;
use App\Models\Producto;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePedido extends CreateRecord
{
    protected static string $resource = PedidoResource::class;

    protected function afterCreate(): void
{
    $pedido = $this->record;

    // Guardar relación manualmente
    $productosData = $this->data['productos'] ?? [];

    $sinStock = false;
    $total = 0;

    foreach ($productosData as $item) {
        $producto = Producto::find($item['producto_id']);
        $cantidad = $item['cantidad'];

        if (!$producto || $producto->stock < $cantidad) {
            $sinStock = true;

            Notification::make()
                ->title("Stock insuficiente para {$producto->nombre}")
                ->body("Stock: {$producto->stock}, solicitado: {$cantidad}")
                ->danger()
                ->send();

            break;
        }

        // Relacionar producto con cantidad
        $pedido->productos()->attach($producto->id, ['cantidad' => $cantidad]);

        // Descontar stock
        $producto->stock -= $cantidad;
        $producto->save();

        // Sumar al total
        $total += $producto->precio * $cantidad;
    }

    if ($sinStock) {
        $pedido->delete(); // Revertir
        return;
    }

    // Guardar el total final
    $pedido->update(['total' => $total]);
}
}
