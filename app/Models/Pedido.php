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
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_productos')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }

      protected static function booted()
    {
        static::deleting(function (Pedido $pedido) {
            foreach ($pedido->productos as $producto) {
                $producto->stock += $producto->pivot->cantidad;
                $producto->save();
            }
        });
    }
}
