<?php

namespace App;

use App\Transaction;

class Buyer extends User // estos modelos extenderán de User ya que un usuario puede ser vendedor o cliente
{
    public function transactions() // retornará la relación de un comprador tiene muchas transacciones
    {
        return $this->hasMany(Transaction::class);
    }
}
