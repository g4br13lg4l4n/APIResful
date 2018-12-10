<?php

namespace App\Http\Controllers\Product;

use App\Product;
use Firebase\FirebaseLib;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class ProductFirebaseController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $firebase = new FirebaseLib(env('FIREBASE_URL', 'null'), env('FIREBASE_TOKEN', 'null'));
        $products = $firebase->get(env('FIREBASE_PATH', 'null').'/products/');
        return response()->json(json_decode($products), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $firebase = new FirebaseLib(env('FIREBASE_URL', 'null'), env('FIREBASE_TOKEN', 'null'));
        $product = $firebase->get(env('FIREBASE_PATH', 'null').'/products/'.$id);
        return response()->json(json_decode($product), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $firebase = new FirebaseLib(env('FIREBASE_URL', 'null'), env('FIREBASE_TOKEN', 'null'));
        $product = $firebase->update(env('FIREBASE_PATH', 'null').'/products/'.$id, $request->all());
        return response()->json(json_decode($product), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $firebase = new FirebaseLib(env('FIREBASE_URL', 'null'), env('FIREBASE_TOKEN', 'null'));
        $product = $firebase->delete(env('FIREBASE_PATH', 'null').'/products/'.$id);

        return response()->json(json_decode($product), 201);
    }
}
