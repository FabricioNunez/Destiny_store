<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PedidoPdfController;
use App\Http\Controllers\MercadoPagoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard y perfil de usuario
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Tienda y catálogo
|--------------------------------------------------------------------------
*/

Route::get('/', [CartController::class, 'catalogo'])
    ->name('tienda.catalogo');

/*
|--------------------------------------------------------------------------
| Carrito de compras
|--------------------------------------------------------------------------
*/

Route::get('/carrito', [CartController::class, 'carrito'])
    ->name('carrito.ver');

Route::post('/carrito/agregar/{producto}', [CartController::class, 'agregar'])
    ->name('carrito.agregar');

Route::patch('/carrito/actualizar/{producto}', [CartController::class, 'actualizar'])
    ->name('carrito.actualizar');

Route::delete('/carrito/eliminar/{producto}', [CartController::class, 'eliminar'])
    ->name('carrito.eliminar');

Route::delete('/carrito/vaciar', [CartController::class, 'vaciar'])
    ->name('carrito.vaciar');

/*
|--------------------------------------------------------------------------
| Checkout
|--------------------------------------------------------------------------
*/

Route::get('/checkout', [CartController::class, 'checkout'])
    ->name('checkout');

Route::post('/checkout', [CartController::class, 'procesarCheckout'])
    ->name('checkout.procesar');

Route::post('/checkout/reintentar', [CartController::class, 'reintentarPago'])
    ->name('checkout.reintentar');

Route::get('/checkout/confirmacion', [CartController::class, 'confirmacion'])
    ->name('checkout.confirmacion');

/*
|--------------------------------------------------------------------------
| PDF de pedidos
|--------------------------------------------------------------------------
*/

Route::get('/pedidos/{pedido}/pdf', [PedidoPdfController::class, 'descargar'])
    ->name('pedidos.pdf');

/*
|--------------------------------------------------------------------------
| Autenticación Laravel Breeze
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Mercado Pago
|--------------------------------------------------------------------------
*/

Route::get('/mercadopago/success', [MercadoPagoController::class, 'success'])
    ->name('mercadopago.success');

Route::get('/mercadopago/failure', [MercadoPagoController::class, 'failure'])
    ->name('mercadopago.failure');

Route::get('/mercadopago/pending', [MercadoPagoController::class, 'pending'])
    ->name('mercadopago.pending');

Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'webhook'])
    ->name('mercadopago.webhook');

/*
|--------------------------------------------------------------------------
| Diagnóstico temporal de sesiones
|--------------------------------------------------------------------------
*/

Route::get('/diagnostico-sesion', function () {
    return response()->json([
        'session_driver' => config('session.driver'),
        'session_cookie' => config('session.cookie'),
        'session_domain' => config('session.domain'),
        'session_secure' => config('session.secure'),
        'session_path' => config('session.path'),
        'session_started' => session()->isStarted(),
    ]);
});

/*
|--------------------------------------------------------------------------
| Diagnóstico temporal de cookies
|--------------------------------------------------------------------------
| Comprueba si Laravel puede enviar una cookie al navegador.
| Esta ruta debe eliminarse después de solucionar el error 419.
|--------------------------------------------------------------------------
*/

Route::get('/diagnostico-cookie', function () {
    return response()
        ->json([
            'mensaje' => 'Prueba de envio de cookies',
            'session_started' => session()->isStarted(),
        ])
        ->cookie(
            'prueba_destiny',
            'funciona',
            5,
            '/',
            null,
            true,
            true,
            false,
            'lax'
        );
});
