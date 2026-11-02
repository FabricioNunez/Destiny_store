<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventarioService
{
    public function cancelarPedido(int $pedidoId): bool
    {
        return DB::transaction(function () use ($pedidoId) {

            // Bloquear el pedido para evitar cancelaciones simultáneas.
            $pedido = Pedido::whereKey($pedidoId)
                ->lockForUpdate()
                ->firstOrFail();

            // Si ya se devolvió el stock, no repetir la operación.
            if ($pedido->stock_released_at !== null) {
                return false;
            }

            // Solo cancelar pedidos pendientes.
            if ($pedido->estado !== 'pendiente') {
                throw new RuntimeException(
                    'Solo se pueden cancelar pedidos pendientes.'
                );
            }

            // Los pedidos con una preferencia de Mercado Pago
            // requieren verificar primero que no exista
            // un pago aprobado o en procesamiento.
            if ($pedido->mercadopago_preference_id !== null) {
                throw new RuntimeException(
                    'Este pedido tiene una operación de Mercado Pago. '
                    . 'Se debe verificar su estado antes de cancelarlo.'
                );
            }

            $productos = $pedido->productos()
                ->orderBy('productos.id')
                ->get();

            foreach ($productos as $producto) {
                Producto::whereKey($producto->id)
                    ->increment(
                        'stock',
                        (int) $producto->pivot->cantidad
                    );
            }

            // Registrar la devolución y cancelar el pedido.
            $pedido->stock_released_at = now();
            $pedido->estado = 'cancelado';
            $pedido->save();

            return true;
        });
    }
}
