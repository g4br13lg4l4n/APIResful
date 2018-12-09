<?php

namespace App;

use App\Product;
use App\Scopes\SellerScope;

class Seller extends User // estos modelos extenderán de User ya que un usuario puede ser vendedor o cliente
{
    
    protected static function boot() // construir e inicializar el modelo en este caso lo usaremos para indicar que Scope utilizar App\Scope\BuyerScope.php
    {   
        parent::boot();
        static::addGlobalScope(new SellerScope); // le decimos que Scope usar
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
