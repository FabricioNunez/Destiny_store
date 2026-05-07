<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Destiny Store</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="font-family: 'Figtree', sans-serif;">

    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-dark px-3 py-5">

        <div class="auth-wrapper w-100" style="max-width: 460px;">

            <div class="text-center mb-4">
                <a href="/" class="text-decoration-none">
                    <div class="fw-bold text-warning fs-2">
                        Destiny Store
                    </div>
                    <div class="text-white-50">
                        Tienda online
                    </div>
                </a>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                {{ $slot }}
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('tienda.catalogo') }}" class="text-white-50 text-decoration-none">
                    Volver a la tienda
                </a>
            </div>

        </div>

    </div>

</body>
</html>