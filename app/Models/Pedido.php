<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    protected static function booted()
    {
        static::deleting(function (Pedido $pedido) {

            // Evitar devolver stock más de una vez.
            if ($pedido->stock_released_at !== null) {
                return;
            }

            // Bloquear los productos mientras se devuelve el stock.
            foreach ($pedido->productos as $producto) {
                Producto::whereKey($producto->id)
                    ->increment('stock', $producto->pivot->cantidad);
            }
        });
    }
}
