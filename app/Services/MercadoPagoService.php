<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use RuntimeException;

class MercadoPagoService
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(
            config('services.mercadopago.access_token')
        );
    }

    public function crearPreferencia(Pedido $pedido)
    {
        $pedido->load('productos');

        $items = [];
        $totalCalculado = 0;

        foreach ($pedido->productos as $producto) {
            $cantidad = (int) $producto->pivot->cantidad;
            $precio = (float) $producto->precio;

            $items[] = [
                'id' => (string) $producto->id,
                'title' => $producto->nombre,
                'quantity' => $cantidad,
                'unit_price' => $precio,
                'currency_id' => 'ARS',
            ];

            $totalCalculado += $precio * $cantidad;
        }

        // Comprobar que el importe coincida con el pedido.
        if (abs($totalCalculado - (float) $pedido->total) > 0.001) {
            throw new RuntimeException(
                'El total de los productos no coincide con el total del pedido.'
            );
        }

        $client = new PreferenceClient();

        $preferencia = $client->create([
            'items' => $items,
            'external_reference' => (string) $pedido->id,
             'metadata' => [
            'pedido_id' => (string) $pedido->id,
                ],
            'back_urls' => [
                'success' => rtrim(config('app.url_publica'), '/')
                    . '/mercadopago/success',

                'failure' => rtrim(config('app.url_publica'), '/')
                    . '/mercadopago/failure',

                'pending' => rtrim(config('app.url_publica'), '/')
                    . '/mercadopago/pending',
            ],
            'auto_return' => 'approved',
        ]);

        $pedido->update([
            'mercadopago_preference_id' => $preferencia->id,
        ]);

        return $preferencia;
    }

    public function consultarPago(string $paymentId)
    {
        $client = new PaymentClient();

        return $client->get($paymentId);
    }

    
public function recuperarPreferencia(Pedido $pedido): string
{
    if (
        $pedido->estado !== 'pendiente'
        || $pedido->stock_released_at !== null
    ) {
        throw new RuntimeException(
            'El pedido ya no está disponible para pagar.'
        );
    }

    if (empty($pedido->mercadopago_preference_id)) {
        throw new RuntimeException(
            'El pedido no tiene una preferencia registrada.'
        );
    }

    // Consultamos directamente la API de Mercado Pago.
    $respuesta = \Illuminate\Support\Facades\Http::withToken(
        config('services.mercadopago.access_token')
    )->acceptJson()->get(
        'https://api.mercadopago.com/checkout/preferences/'
        . $pedido->mercadopago_preference_id
    );

    $respuesta->throw();

    // Verificamos que sea la preferencia de este pedido.
    if (
        (string) $respuesta->json('id') !==
            (string) $pedido->mercadopago_preference_id
        || (string) $respuesta->json('external_reference') !==
            (string) $pedido->id
    ) {
        throw new RuntimeException(
            'La preferencia no corresponde al pedido.'
        );
    }

    $urlPago = $respuesta->json('init_point');

    if (empty($urlPago)) {
        throw new RuntimeException(
            'La preferencia no tiene una URL de pago disponible.'
        );
    }

    return $urlPago;
}

public function consultarPagosDePreferencia(Pedido $pedido): array
{
    if (empty($pedido->mercadopago_preference_id)) {
        throw new RuntimeException(
            'El pedido no tiene una preferencia registrada.'
        );
    }

    $respuesta = \Illuminate\Support\Facades\Http::withToken(
        config('services.mercadopago.access_token')
    )->acceptJson()->get(
        'https://api.mercadopago.com/v1/payments/search',
        [
            'external_reference' => (string) $pedido->id,
            'sort' => 'date_created',
            'criteria' => 'desc',
            'limit' => 50,
        ]
    );

    $respuesta->throw();

    $pagos = $respuesta->json('results', []);

    if (!is_array($pagos)) {
        throw new RuntimeException(
            'Mercado Pago devolvió una respuesta inesperada.'
        );
    }

    return $pagos;
}

    public function verificarPago(string $paymentId): ?Pedido
    {
        // Consultar el pago directamente en Mercado Pago.
        $pago = $this->consultarPago($paymentId);

        // Solo procesar pagos aprobados.
        if ($pago->status !== 'approved') {
            return null;
        }

        // Identificar el pedido asociado.
        $pedidoId = filter_var(
            $pago->external_reference ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$pedidoId || $pedidoId < 1) {
            throw new RuntimeException(
                'El pago no contiene una referencia válida al pedido.'
            );
        }

        return DB::transaction(function () use (
            $pago,
            $paymentId,
            $pedidoId
        ) {
            // Bloquear el pedido durante la verificación.
            $pedido = Pedido::whereKey($pedidoId)
                ->lockForUpdate()
                ->firstOrFail();

            
// El pedido debe tener una preferencia registrada.
if (empty($pedido->mercadopago_preference_id)) {
    throw new RuntimeException(
        'El pedido no tiene una preferencia de Mercado Pago.'
    );
}

// Comprobar la preferencia asociada al pago.
// No confundir preference_id con order.id.

            // Verificar la preferencia cuando Mercado Pago
            // proporciona explícitamente su identificador.
            $preferenceId = $pago->preference_id ?? null;

            if (
                $preferenceId !== null
                && (string) $preferenceId !==
                    (string) $pedido->mercadopago_preference_id
            ) {
                throw new RuntimeException(
                    'El pago pertenece a otra preferencia.'
                );
            }

            // El ID de order no necesariamente equivale al ID
            // de preference, por lo que no se comparan directamente.
            // La vinculación se valida mediante external_reference
            // y los controles de importe, moneda y estado.

            // Evitar asociar otro pago a un pedido ya acreditado.
            if (
                $pedido->mercadopago_payment_id !== null
                && (string) $pedido->mercadopago_payment_id !== $paymentId
            ) {
                throw new RuntimeException(
                    'El pedido ya tiene asociado otro pago.'
                );
            }

            // Validar el importe.
            if (
                abs(
                    (float) $pago->transaction_amount
                    - (float) $pedido->total
                ) > 0.01
            ) {
                throw new RuntimeException(
                    'El importe del pago no coincide con el pedido.'
                );
            }

            // Validar la moneda.
            if ($pago->currency_id !== 'ARS') {
                throw new RuntimeException(
                    'La moneda del pago no es ARS.'
                );
            }
            
            // Verificar que el pago pertenezca a nuestra cuenta.
            $sellerId = config('services.mercadopago.seller_id');

            if (
                !is_scalar($sellerId)
                || (string) $sellerId === ''
            ) {
                throw new RuntimeException(
                    'No está configurado el vendedor de Mercado Pago.'
                );
            }

            $collectorId = $pago->collector_id ?? null;

            if (
                $collectorId === null
                || (string) $collectorId !== (string) $sellerId
            ) {
                throw new RuntimeException(
                    'El pago no pertenece a la cuenta vendedora configurada.'
                );
            }


            // Impedir acreditar pedidos cancelados
            // o cuyo stock ya fue liberado.
            if (
                $pedido->estado === 'cancelado'
                || $pedido->stock_released_at !== null
            ) {
                throw new RuntimeException(
                    'El pedido está cancelado o su stock fue liberado.'
                );
            }

            // Si el pago ya estaba registrado, no repetir cambios.
            if (
                (string) $pedido->mercadopago_payment_id === $paymentId
                && $pedido->estado === 'pagado'
            ) {
                return $pedido;
            }

            // Registrar el pago aprobado.
            $pedido->mercadopago_payment_id = $paymentId;
            $pedido->estado = 'pagado';
            $pedido->save();

            return $pedido;
        });
    }
}
