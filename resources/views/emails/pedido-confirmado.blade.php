<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pago confirmado - Destiny Store</title>
</head>

<body style="margin:0; padding:30px 15px; background:#f3f4f6; font-family:Arial,sans-serif; color:#222;">

    <div style="max-width:600px; margin:auto; background:#fff; border-radius:12px; overflow:hidden;">

        <div style="background:#171717; padding:25px; text-align:center;">
            <h1 style="color:#d4af37; margin:0;">
                Destiny Store
            </h1>
        </div>

        <div style="padding:30px;">

            <h2 style="color:#198754;">
                ¡Tu pago fue aprobado!
            </h2>

            <p>
                Hola, {{ $pedido->cliente->nombre }}.
            </p>

            <p>
                Recibimos correctamente el pago de tu compra.
                A continuación encontrarás los detalles de tu pedido.
            </p>

            <div style="background:#f8f9fa; padding:20px; border-radius:8px; margin:25px 0;">

                <p>
                    <strong>Número de pedido:</strong>
                    #{{ $pedido->id }}
                </p>

                <p>
                    <strong>Estado:</strong>
                    Pagado
                </p>

                <p>
                    <strong>Total:</strong>
                    ${{ number_format($pedido->total, 2, ',', '.') }}
                </p>

            </div>

            <p>
                Adjuntamos el comprobante de tu pedido en formato PDF.
            </p>

            <p>
                ¡Gracias por elegir Destiny Store!
            </p>

        </div>

        <div style="background:#f8f9fa; padding:18px; text-align:center; font-size:12px; color:#777;">
            Destiny Store · Confirmación de compra
        </div>

    </div>

</body>
</html>
