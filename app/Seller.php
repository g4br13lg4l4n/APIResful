<?php

namespace App;

use App\Product;

class Seller extends User // estos modelos extenderán de User ya que un usuario puede ser vendedor o cliente
{
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
