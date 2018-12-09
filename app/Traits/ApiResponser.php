<?php

// Cada controller extiende de este Traits para omologar todas las respuesta de la API y así no repetir código al reponder cada petición
namespace App\Traits;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponser
{
	private function successResponse($data, $code)
	{
		return response()->json($data, $code);
	}
	protected function errorResponse($message, $code)
	{
		return response()->json(['error' => $message, 'code' => $code], $code);
	}
	protected function showAll(Collection $collection, $code = 200) // esta tendrá que recibir una colección de datos, por ejemplo la lista de todos lo usuarios
	{
		return $this->successResponse(['data' => $collection], $code);
	}
	protected function showOne(Model $instance, $code = 200) // esta tendrá que recibir un modelo por ejemplo los datos de un usuario
	{
		return $this->successResponse(['data' => $instance], $code);
	}
}