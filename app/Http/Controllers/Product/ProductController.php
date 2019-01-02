<?php

namespace App\Http\Controllers\Product;

use Log;
use App\User;
use App\Product;
use Firebase\FirebaseLib;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class ProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();
        return $this->showAll($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'quantity' => 'required',
        ];
        $this->validate($request, $rules);
        $request['image'] = array_rand(['1.jpg','2.jpg','3.jpg']);
        $request['status'] = Product::PRODUCTO_DISPONIBLE;
        $request['seller_id'] = User::all()->random()->id;

        $product = Product::create($request->all());

        if($product){
            // add product to firebase
            try {
                $this->ConnectionFirebase()->set(env('FIREBASE_PATH', 'null').'/products/'.$product->id, $product);
            } catch (Exception $e) {
                Log::info('Error al guardar este usuario en firebase => '. $request->name);
                Log::info($e);
            }
        }
        return $this->showOne($product, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return $this->showOne($product);
    }

}
