<?php

namespace App\Http\Controllers\Seller;

use App\Seller;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class SellerCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        $categories = $seller->products() 
                        ->whereHas('categories')
                        ->with('categories')
                        ->get()
                        ->pluck('categories')
                        ->collapse()
                        ->unique('id') // ya tenida la lista si no queremos repetir los datos le decimos que los distinga por id y no los repita
                        ->values(); // ya echo esto agregamos para eliminar datos vacios

        return $this->showAll($categories);
    }
}
