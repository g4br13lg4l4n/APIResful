<?php
namespace App\Scopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class BuyerScope implements Scope
{
  public function apply(Builder $builder, Model $model) // apply es quien nos inicia nuestro scope, apply modificará la consulta del modelo y agregar el has('transactions') para buyer
  {
    $builder->has('transactions');
  }
}