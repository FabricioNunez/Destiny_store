<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedido #{{ $pedido->id }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
        }

        .header p {
            margin: 5px 0 0;
            color: #666;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f0f0f0;
            text-align: left;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .text-right {
            text-align: right;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            background: #ffc107;
            color: #222;
            border-radius: 4px;
        }

        .footer {
            margin-top: 35px;
            text-align: center;
            color: #777;
            font-size: 11px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Destiny Store</h1>
        <p>Comprobante de pedido</p>
    </div>

    <div class="section">
        <div class="section-title">Datos del pedido</div>

        <p><strong>Número de pedido:</strong> #{{ $pedido->id }}</p>
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y H:i') }}</p>
        <p><strong>Estado:</strong> <span class="badge">{{ ucfirst($pedido->estado) }}</span></p>
    </div>

    <div class="section">
        <div class="section-title">Datos del cliente</div>

        <p><strong>Nombre:</strong> {{ $pedido->cliente->nombre }}</p>
        <p><strong>Email:</strong> {{ $pedido->cliente->email }}</p>
        <p><strong>Teléfono:</strong> {{ $pedido->cliente->telefono ?? 'No informado' }}</p>
        <p><strong>Dirección:</strong>{{ $pedido->cliente->direccion }}</p>
    </div>

    <div class="section">
        <div class="section-title">Productos</div>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio unitario</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @foreach($pedido->productos as $producto)
                    <tr>
                        <td>{{ $producto->nombre }}</td>
                        <td class="text-right">{{ $producto->pivot->cantidad }}</td>
                        <td class="text-right">${{ number_format($producto->precio, 2, ',', '.') }}</td>
                        <td class="text-right">
                            ${{ number_format($producto->precio * $producto->pivot->cantidad, 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total">
            Total: ${{ number_format($pedido->total, 2, ',', '.') }}
        </div>
    </div>

    <div class="footer">
        Este comprobante fue generado automáticamente por Destiny Store.
    </div>

</body>
</html>