<?php

namespace App\Filament\Resources\PedidoResource\Pages;

use App\Filament\Resources\PedidoResource;
use App\Models\Producto;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePedido extends CreateRecord
{
    protected static string $resource = PedidoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $total = 0;

        foreach ($data['productos'] as $producto) {
            $productoModel = Producto::find($producto['producto_id']);

            if (!$productoModel) {
                Notification::make()
                    ->title('Producto no encontrado')
                    ->danger()
                    ->send();
                $this->halt();
            }

            // Validar stock suficiente
            if ($productoModel->stock < $producto['cantidad']) {
                Notification::make()
                    ->title("Stock insuficiente para {$productoModel->nombre}")
                    ->body("Stock disponible: {$productoModel->stock}, solicitado: {$producto['cantidad']}")
                    ->danger()
                    ->send();

                $this->halt();
            }

            // Restar stock
            $productoModel->stock -= $producto['cantidad'];
            $productoModel->save();

            $total += $productoModel->precio * $producto['cantidad'];
        }

        $data['total'] = $total;

        return $data;
    }
}
