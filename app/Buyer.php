<?php

namespace App;
use App\Transaction;
use App\Scopes\BuyerScope;
//use App\Transformers\BuyerTransformer;

class Buyer extends User // estos modelos extenderán de User ya que un usuario puede ser vendedor o cliente
{

    protected static function boot() // construir e inicializar el modelo en este caso lo usaremos para indicar que Scope utilizar App\Scope\BuyerScope.php
    {   
        parent::boot();
        static::addGlobalScope(new BuyerScope); // le decimos que Scope usar
    }
    public function transactions() // retornará la relación de un comprador tiene muchas transacciones
    {
        return $this->hasMany(Transaction::class);
    }
}
