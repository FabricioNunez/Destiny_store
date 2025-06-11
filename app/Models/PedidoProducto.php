<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoProducto extends Model
{
    public function pedidos()
{
    return $this->belongsToMany(Pedido::class, 'pedido_productos')
                ->withPivot('cantidad')
                ->withTimestamps();
}

}
