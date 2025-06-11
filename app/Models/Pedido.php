<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
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

}
