<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    public function success(Request $request)
    {
        return redirect()
            ->route('tienda.catalogo')
            ->with(
                'success',
                'Mercado Pago recibió tu operación. Estamos verificando el pago.'
            );
    }

    public function failure(Request $request)
    {
        return redirect()
            ->route('tienda.catalogo')
            ->with(
                'error',
                'El pago no se completó. Podés consultar el estado de tu pedido antes de volver a intentar.'
            );
    }

    public function pending(Request $request)
    {
        return redirect()
            ->route('tienda.catalogo')
            ->with(
                'success',
                'Tu pago está pendiente de confirmación.'
            );
    }

    public function webhook(
        Request $request,
        MercadoPagoService $mercadoPago
    ) {
        $secret = config('services.mercadopago.webhook_secret');

        $signature = $request->header('x-signature', '');
        $requestId = $request->header('x-request-id', '');
        $dataId = $request->query('data.id');

        // Validar los datos necesarios para autenticar el webhook.
        if (
            !is_string($secret) || $secret === '' ||
            !is_string($dataId) || $dataId === '' ||
            !is_string($signature) || $signature === '' ||
            !is_string($requestId) || $requestId === ''
        ) {
            return response()->json([
                'error' => 'Notificación no autenticada',
            ], 401);
        }

        // Extraer los componentes de x-signature.
        $partes = [];

        foreach (explode(',', $signature) as $parte) {
            $par = explode('=', trim($parte), 2);

            if (count($par) === 2) {
                $partes[trim($par[0])] = trim($par[1]);
            }
        }

        $timestamp = $partes['ts'] ?? null;
        $firmaRecibida = $partes['v1'] ?? null;

        if (
            !is_string($timestamp) ||
            !ctype_digit($timestamp) ||
            !is_string($firmaRecibida) ||
            !preg_match('/^[a-fA-F0-9]{64}$/', $firmaRecibida)
        ) {
            return response()->json([
                'error' => 'Firma inválida',
            ], 401);
        }

        // Mercado Pago utiliza el ID en minúsculas
        // para construir el mensaje firmado.
        $idFirmado = strtolower($dataId);

        $manifest = "id:{$idFirmado};request-id:{$requestId};ts:{$timestamp};";

        $firmaEsperada = hash_hmac(
            'sha256',
            $manifest,
            $secret
        );

        if (!hash_equals(strtolower($firmaEsperada), strtolower($firmaRecibida))) {
    Log::warning('Mercado Pago: firma de webhook no coincide', [
        'data_id' => $dataId,
        'request_id_presente' => $requestId !== '',
        'signature_presente' => $signature !== '',
    ]);

    return response()->json(['error' => 'Firma inválida'], 401);
    }

        // Procesar únicamente eventos de pagos.
        $tipo = $request->input('type')
            ?? $request->query('type');

        $accion = $request->input('action')
            ?? $request->query('action');

        if (
            $tipo !== 'payment' &&
            !in_array($accion, [
                'payment.created',
                'payment.updated',
            ], true)
        ) {
            return response()->json([
                'received' => true,
                'processed' => false,
            ], 200);
        }

        // El ID debe tener el formato esperado para un pago.
        if (!ctype_digit($dataId)) {
            return response()->json([
                'error' => 'ID de pago inválido',
            ], 400);
        }

        try {
            // Consultar la API de Mercado Pago.
            // Nunca confiar en el estado enviado por el webhook.
            $pedido = $mercadoPago->verificarPago($dataId);

            if ($pedido !== null) {
                Log::info('Pago aprobado verificado', [
                    'pedido_id' => $pedido->id,
                    'payment_id' => $dataId,
                ]);
            } else {
                Log::info('Pago todavía no aprobado', [
                    'payment_id' => $dataId,
                ]);
            }

            return response()->json([
                'received' => true,
                'processed' => $pedido !== null,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error al verificar pago de Mercado Pago', [
                'payment_id' => $dataId,
                'error' => $e->getMessage(),
            ]);

            // Permitir que Mercado Pago reintente
            // la notificación ante errores temporales.
            return response()->json([
                'error' => 'No se pudo procesar la notificación',
            ], 500);
        }
    }
}
