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
        $arrayImages = ['1.jpg','2.jpg','3.jpg'];
        $image = array_rand($arrayImages);
        $request['image'] = $arrayImages[$image];
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
        $productFirebase = $this->ConnectionFirebase()->get(env('FIREBASE_PATH', 'null').'/products/'.$product->id);
        // know if the product is in firebase or not
        if($productFirebase == "null") {
            $product->setAttribute('firebase', 'false'); 
            return $this->showOne($product);
        }else{
            $product->setAttribute('firebase', 'true');
            return $this->showOne($product);
        }
    }

}
