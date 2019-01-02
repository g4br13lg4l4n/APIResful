<?php

namespace App\Http\Controllers\Seller;

use App\Seller;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class SellerTransactionController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        $transactions = $seller->products() // obtenemos los productos del comprador
                        ->whereHas('transactions') // obtenemos solo los que tengas transacciones
                        ->with('transactions') // le decimos que solo los que tengas transacciones queremos obtener
                        ->get() // obtenemos la colección
                        ->pluck('transactions') // de esta colección solo queremos el dato de las transacciones
                        ->collapse(); // unimos las colecciones en una lista 

        return $this->showAll($transactions);
    }
}
