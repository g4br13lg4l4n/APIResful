<?php
/**
 * ApiController será usado en cada unos de nuestros contraladores y este a la vez usuará Traits 'ApiResponser' esto para que 
 * cada controlador pueda extender de las funcionalidades sin tener que repetir código
 */
namespace App\Http\Controllers;

use App\Traits\ApiResponser; // importamos el Trait
use Illuminate\Http\Request;
use App\Traits\FirebaseConnection;

class ApiController extends Controller
{
    use ApiResponser, FirebaseConnection; // declaramos que lo usaremos

}
