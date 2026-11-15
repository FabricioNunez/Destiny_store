<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'fecha',
        'estado',
        'total',
        'mercadopago_preference_id',
        'mercadopago_payment_id',
    ];

    protected $casts = [
        'stock_released_at' => 'datetime',
        'comprobante_enviado_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function productos()
    {
        return $this->belongsToMany(
            Producto::class,
            'pedido_productos'
        )
            ->withPivot('cantidad')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminación de pedidos
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::deleting(function (Pedido $pedido) {

            // Devolver stock solamente si no fue reintegrado antes.
            if ($pedido->stock_released_at === null) {

                foreach ($pedido->productos as $producto) {

                    Producto::whereKey($producto->id)
                        ->increment(
                            'stock',
                            (int) $producto->pivot->cantidad
                        );
                }
            }

            // Eliminar las relaciones del pedido con sus productos.
            // Esto no elimina los productos del catálogo.
            $pedido->productos()->detach();
        });
    }
}
