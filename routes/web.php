<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PedidoPdfController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/', [CartController::class, 'catalogo'])->name('tienda.catalogo');

Route::get('/carrito', [CartController::class, 'carrito'])->name('carrito.ver');

Route::post('/carrito/agregar/{producto}', [CartController::class, 'agregar'])->name('carrito.agregar');

Route::patch('/carrito/actualizar/{producto}', [CartController::class, 'actualizar'])->name('carrito.actualizar');

Route::delete('/carrito/eliminar/{producto}', [CartController::class, 'eliminar'])->name('carrito.eliminar');

Route::delete('/carrito/vaciar', [CartController::class, 'vaciar'])->name('carrito.vaciar');

Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');

Route::post('/checkout', [CartController::class, 'procesarCheckout'])->name('checkout.procesar');

Route::get('/checkout/confirmacion', [CartController::class, 'confirmacion'])->name('checkout.confirmacion');

Route::get('/pedidos/{pedido}/pdf', [PedidoPdfController::class, 'descargar'])
    ->name('pedidos.pdf');
require __DIR__.'/auth.php';
