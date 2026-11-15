<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MercadoPagoService;

class CartController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Catálogo de productos
    |--------------------------------------------------------------------------
    */

    public function catalogo()
    {
        $productos = Producto::where('stock', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('tienda.catalogo', compact('productos'));
    }

    /*
    |--------------------------------------------------------------------------
    | Mostrar carrito
    |--------------------------------------------------------------------------
    */

    public function carrito()
    {
        // Comprobar si Mercado Pago ya confirmó el último pedido.
        $pedidoId = session()->get('ultimo_pedido_id');

        if ($pedidoId) {
            $pedido = Pedido::find($pedidoId);

            if ($pedido && $pedido->estado === 'pagado') {
                // Eliminar los productos de la compra confirmada.
                session()->forget([
                    'carrito',
                    'ultimo_pedido_id',
                ]);

                return redirect()
                    ->route('tienda.catalogo')
                    ->with(
                        'success',
                        '¡Tu compra fue confirmada! Gracias por comprar en Destiny Store.'
                    );
            }
        }

        $carrito = session()->get('carrito', []);

        // Actualizar el stock de cada producto desde MySQL.
        // Evita mostrar cantidades de stock antiguas.
        foreach ($carrito as $productoId => &$item) {
            $producto = Producto::find($productoId);

            $item['stock'] = $producto
                ? $producto->stock
                : 0;
        }

        unset($item);

        // Conservar los datos actualizados en la sesión.
        session()->put('carrito', $carrito);

        return view('tienda.carrito', compact('carrito'));
    }

    /*
    |--------------------------------------------------------------------------
    | Agregar productos al carrito
    |--------------------------------------------------------------------------
    */

    public function agregar(Request $request, Producto $producto)
    {
        $cantidad = (int) $request->input('cantidad', 1);

        if ($cantidad < 1) {
            $cantidad = 1;
        }

        if ($cantidad > $producto->stock) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'No hay suficiente stock disponible.'
                );
        }

        $carrito = session()->get('carrito', []);

        if (isset($carrito[$producto->id])) {
            $nuevaCantidad = $carrito[$producto->id]['cantidad']
                + $cantidad;

            if ($nuevaCantidad > $producto->stock) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'No puedes agregar más unidades que el stock disponible.'
                    );
            }

            $carrito[$producto->id]['cantidad'] = $nuevaCantidad;

            // Mantener el stock actualizado.
            $carrito[$producto->id]['stock'] = $producto->stock;

        } else {
            $carrito[$producto->id] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
                'imagen' => $producto->imagen,
                'stock' => $producto->stock,
                'cantidad' => $cantidad,
            ];
        }

        session()->put('carrito', $carrito);

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Producto agregado al carrito.');
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar cantidades
    |--------------------------------------------------------------------------
    */

    public function actualizar(Request $request, Producto $producto)
    {
        $cantidad = (int) $request->input('cantidad', 1);

        if ($cantidad < 1) {
            $cantidad = 1;
        }

        if ($cantidad > $producto->stock) {
            $cantidad = $producto->stock;
        }

        $carrito = session()->get('carrito', []);

        if (isset($carrito[$producto->id])) {
            $carrito[$producto->id]['cantidad'] = $cantidad;
            $carrito[$producto->id]['stock'] = $producto->stock;

            session()->put('carrito', $carrito);
        }

        $total = collect($carrito)->sum(function ($item) {
            return $item['precio'] * $item['cantidad'];
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'cantidad' => $cantidad,
                'subtotal' => $producto->precio * $cantidad,
                'total' => $total,
            ]);
        }

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Carrito actualizado.');
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar producto del carrito
    |--------------------------------------------------------------------------
    */

    public function eliminar(Producto $producto)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$producto->id])) {
            unset($carrito[$producto->id]);

            session()->put('carrito', $carrito);
        }

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Producto eliminado del carrito.');
    }

    /*
    |--------------------------------------------------------------------------
    | Vaciar carrito manualmente
    |--------------------------------------------------------------------------
    */

    public function vaciar()
    {
        session()->forget('carrito');

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Carrito vaciado.');
    }

    /*
    |--------------------------------------------------------------------------
    | Mostrar checkout
    |--------------------------------------------------------------------------
    */

    public function checkout()
    {
        $carrito = session()->get('carrito', []);

        if (count($carrito) === 0) {
            return redirect()
                ->route('tienda.catalogo')
                ->with('error', 'Tu carrito está vacío.');
        }

        $total = collect($carrito)->sum(function ($item) {
            return $item['precio'] * $item['cantidad'];
        });

        return view(
            'tienda.checkout',
            compact('carrito', 'total')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Procesar checkout
    |--------------------------------------------------------------------------
    */

    public function procesarCheckout(
        Request $request,
        MercadoPagoService $mercadoPago
    ) {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:50',
            'direccion' => ['required', 'string', 'max:255'],
        ]);

        // Comprobar si esta sesión tiene un pedido pendiente.
        $pedidoAnteriorId = session()->get('ultimo_pedido_id');

        if ($pedidoAnteriorId) {
            $pedidoAnterior = Pedido::find($pedidoAnteriorId);

            if (
                $pedidoAnterior
                && $pedidoAnterior->estado === 'pendiente'
                && $pedidoAnterior->stock_released_at === null
            ) {
                try {
                    // Reutilizar el pedido existente.
                    // No crear otro ni volver a descontar stock.
                    $preferencia = $mercadoPago->crearPreferencia(
                        $pedidoAnterior
                    );

                    if (empty($preferencia->init_point)) {
                        throw new \RuntimeException(
                            'Mercado Pago no devolvió una URL de pago.'
                        );
                    }

                    return redirect()->away(
                        $preferencia->init_point
                    );

                } catch (\Throwable $e) {
                    report($e);

                    return redirect()
                        ->route('checkout')
                        ->with(
                            'error',
                            'No pudimos recuperar el pago de tu pedido. Intentá nuevamente más tarde.'
                        );
                }
            }
        }

        $carrito = session()->get('carrito', []);

        if (empty($carrito)) {
            return redirect()
                ->route('tienda.catalogo')
                ->with('error', 'Tu carrito está vacío.');
        }

        try {
            // Crear el pedido y reservar el stock.
            $pedido = DB::transaction(function () use (
                $request,
                $carrito
            ) {
                $cliente = Cliente::firstOrCreate(
                    ['email' => $request->email],
                    [
                        'nombre' => $request->nombre,
                        'telefono' => $request->telefono,
                        'direccion' => $request->direccion,
                    ]
                );

                $productos = [];
                $total = 0;

                foreach ($carrito as $item) {
                    $cantidad = (int) $item['cantidad'];

                    if ($cantidad < 1) {
                        throw new \Exception(
                            'Cantidad de producto inválida.'
                        );
                    }

                    $producto = Producto::whereKey($item['id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($cantidad > $producto->stock) {
                        throw new \Exception(
                            'No hay stock suficiente para '
                            . $producto->nombre
                        );
                    }

                    $productos[] = [
                        'producto' => $producto,
                        'cantidad' => $cantidad,
                    ];

                    $total += $producto->precio * $cantidad;
                }

                $pedido = Pedido::create([
                    'cliente_id' => $cliente->id,
                    'fecha' => now(),
                    'estado' => 'pendiente',
                    'total' => $total,
                ]);

                foreach ($productos as $item) {
                    $producto = $item['producto'];
                    $cantidad = $item['cantidad'];

                    $pedido->productos()->attach(
                        $producto->id,
                        ['cantidad' => $cantidad]
                    );

                    $producto->stock -= $cantidad;
                    $producto->save();
                }

                return $pedido;
            });

            // Guardar el pedido antes de comunicarnos con MP.
            session()->put('ultimo_pedido_id', $pedido->id);

            try {
                // Crear preferencia de pago.
                $preferencia = $mercadoPago->crearPreferencia(
                    $pedido
                );

                if (empty($preferencia->init_point)) {
                    throw new \RuntimeException(
                        'Mercado Pago no devolvió una URL de pago.'
                    );
                }

            } catch (\Throwable $e) {
                report($e);

                // Conservar el pedido para verificar la operación.
                // Mercado Pago podría haber recibido la solicitud.
                return redirect()
                    ->route('checkout')
                    ->with(
                        'error',
                        'No pudimos confirmar el inicio del pago. No vuelvas a intentarlo todavía; estamos verificando la operación.'
                    );
            }

            // Mantener el ID del pedido en sesión.
            session()->put('ultimo_pedido_id', $pedido->id);

            // Redirigir a Mercado Pago Checkout Pro.
            return redirect()->away(
                $preferencia->init_point
            );

        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('checkout')
                ->with(
                    'error',
                    'No pudimos iniciar el pago. Revisá los datos e intentá nuevamente.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Confirmación de pedido
    |--------------------------------------------------------------------------
    */

    public function confirmacion()
    {
        $pedidoId = session()->get('ultimo_pedido_id');

        if (!$pedidoId) {
            return redirect()->route('tienda.catalogo');
        }

        $pedido = Pedido::with([
            'cliente',
            'productos',
        ])->findOrFail($pedidoId);

        return view(
            'tienda.confirmacion',
            compact('pedido')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reintentar pago
    |--------------------------------------------------------------------------
    */

    public function reintentarPago(
        Request $request,
        MercadoPagoService $mercadoPago
    ) {
        $pedidoId = session()->get('ultimo_pedido_id');

        if (!$pedidoId) {
            return redirect()
                ->route('checkout')
                ->with(
                    'error',
                    'No encontramos un pedido para reintentar.'
                );
        }

        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            session()->forget('ultimo_pedido_id');

            return redirect()
                ->route('checkout')
                ->with('error', 'El pedido ya no existe.');
        }

        if (
            $pedido->estado !== 'pendiente'
            || $pedido->stock_released_at !== null
        ) {
            return redirect()
                ->route('tienda.catalogo')
                ->with(
                    'error',
                    'Este pedido ya no está disponible para pagar.'
                );
        }

        // No crear una operación nueva si falta la preferencia.
        if (empty($pedido->mercadopago_preference_id)) {
            return redirect()
                ->route('checkout')
                ->with(
                    'error',
                    'No pudimos recuperar la operación de pago. '
                    . 'Estamos verificando si Mercado Pago la recibió.'
                );
        }

        try {
            $pagos = $mercadoPago->consultarPagosDePreferencia(
                $pedido
            );

            foreach ($pagos as $pago) {
                $estado = $pago['status'] ?? null;

                // No permitir un segundo intento si ya existe
                // un pago aprobado o en proceso.
                if (in_array($estado, [
                    'approved',
                    'pending',
                    'in_process',
                    'authorized',
                ], true)) {
                    return redirect()
                        ->route('tienda.catalogo')
                        ->with(
                            'success',
                            'Ya existe una operación de pago para este pedido. '
                            . 'Estamos esperando su confirmación.'
                        );
                }
            }

            // Recuperar la preferencia original.
            // No se crea otro pedido ni se descuenta stock.
            $urlPago = $mercadoPago->recuperarPreferencia(
                $pedido
            );

            return redirect()->away($urlPago);

        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('checkout')
                ->with(
                    'error',
                    'No pudimos recuperar el pago pendiente. '
                    . 'Intentá nuevamente más tarde.'
                );
        }
    }
}
