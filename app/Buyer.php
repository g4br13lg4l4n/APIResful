<?php

namespace App;

use App\Transaction;
use App\Scopes\BuyerScope;

class Buyer extends User // estos modelos extenderán de User ya que un usuario puede ser vendedor o cliente
{

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new BuyerScope);
    }

    public function transactions() // retornará la relación de un comprador tiene muchas transacciones
    {
        return $this->hasMany(Transaction::class);
    }
}
