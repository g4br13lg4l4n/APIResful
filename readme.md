## Api Laravel
By Gabriel Galán Méndez

## Notas
## Illuminate\Foundation\Exceptions\Handler

Laravel por default cuando encuentra un error en las validaciones retorna a la página anterior al ser una API no tenémos página anterior de retorno por eso sobrepondremos el metedo siguiente en la carpeta app/Exceptions/Handler.php
/**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }

        $errors = $e->validator->errors()->getMessages();

        if ($request->expectsJson()) {
            return response()->json($errors, 422);
        }

        return redirect()->back()->withInput(
            $request->input()
        )->withErrors($errors);
    }
    /**  Nos debe de quedar así  **/
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();
        return $this->errorResponse($errors, 422); // usamos el metodo errorResponse que tiene ApiResponser, al importarlo podemos usar sus funciones
    }

    /** render en Handle.php **/
    si el error que se nos mostrará será por una validación, validamos con exception sea una instancia de ValidationException
    y así usar la función anterior mente sobre escrita (convertValidationExceptionToResponse)

    public function render($request, Exception $exception)
    {
        if($exception instanceof ValidationException)
        {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }
        return parent::render($request, $exception);
    }

  /** Manejando error por no encontar un usuario o dato en una petición (No query results for model [App\User] 23762) **/
  Dentro de nuestro render especificamos que por error de no encontar el dato ModelNotFoundException nos envíe un error tipo json

  public function render($request, Exception $exception)
    {
        if($exception instanceof ModelNotFoundException){
            return $this->errorResponse('No existe instancia con el id especificado', 404);
        }
        return parent::render($request, $exception);
    }

  /** Validaciones **/
  En nuestro archivo Handler obtendremos si el error fue por autentificacion (login) con el metodo AuthenticationException
  cachando el error redirigimos al metodo unauthenticated que lo sobre escribiremos para que el error sea de tipos json 
  protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->errorResponse(['error' => 'Unauthenticated'], 401);
    }

/**  excepciones por autorización (por si el usuario no tiene permisos a urls) **/
  AuthorizationException nos permite saber un error por autorización
  if ($exception instanceof AuthorizationException) // si no tiene autorización en la url
  {
      return $this->errorResponse('No tiene permisos', 403);
  }

/** excepciones por no encontrar la url marcada 404 not found **/
Para cachar este tipo de error al no encontrar una página en nuestro handler en la función de render
con NotFoundHttpException podemos saber si es de este tipo de error y dar una respuesta tipo json
public function render($request, Exception $exception)
  {
    if ($exception instanceof NotFoundHttpException) // si no tiene autorización en la url
    {
        return $this->errorResponse('No se encontró la url especificada', 404);
    }
  }

/**  excepciones por error de metodo si pides por post pero solo permite get  **/
el metodo no esta permitido MethodNotAllowedHttpException este metodo nos permite saber si hay un error al tratar de ejecutar 
un metodo request no permitido

  if ($exception instanceof MethodNotAllowedHttpException)
    {
        return $this->errorResponse('El metodo especificado en la petición no es vaido', 405);
    }

/** Para poder obtener excepciones mas generales con la funcion HttpException **/
if ($exception instanceof HttpException)
    {
        return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
    }
/** para obtener errores al tratar de eliminar un dato relacionado con otras tablas **/
if($exception instanceof QueryException) 
  {
    $codigo = $exception->errorInfo[1]; 
    if($codigo == 1451){
      return $this->errorResponse('No se puedo eliminar porque esta relacionado con otra tabla', 409);
    }  
  }

/**  Para errores de tipos servidor ejemplo: no se puede conectar a la BD **/
es importante como desarollador conocer cual fue la falla por eso debenmos conocer si estamos en modo desarrollo o producción
esto lo conocemos con config('app.debug'), de no ser el caso de estar en desarrollo enviamos un error json al usuario 

if(config('app.debug')) 
  {
      return $this->errorResponse('Falla inisperada, intente luego', 500);
  }
  return parent::render($request, $exception);


/**  Inyección inplicita de modelos **/
La inyección inpicita nos ayuda a reducir código ya que nos permite solo mostrarle el modelo para que este pueda hacer una consulta o metodo desea por ejemplo para buscar un usuario obtenemos el $id y buscamos el usuario 
public function show($id)
    {
        $usuario = User::findOrfail($id);
        return $this->showOne($usuario);
    }
 sin enbargo podemos tan solo decirle que modelo es y este sabrá si encuentra el valor o nos manda una excepción
public function show(User $user) // mostramos el modelo User || inyección de dependencia User
    {
        return $this->showOne($user);
    }
    Nota debemos de tener consistencia en el nombre de cada parametro para que esto pueda funcionar
public function destroy(User $user)
    {
        $user->delete();

        return $this->showOne($user, 201);
    }

/** Global scope **/
Los Scope se crean en una carpeta llamada "Scopes" dentro de app

en este caso lo usaremos para poder trater a nuestros buyers, la solución no fue la misma que de usuarios ya que buyer y seller dependen del modelo User y en el caso de buyer este se convierte en buyer al tener una compra por ellos necesitamos de los scope para ahi poder hacer las retricciones 
creamos el archivo BuyerScope.php dentro de App\Scope
class BuyerScope implements Scope
{
  public function apply(Builder $builder, Model $model)  // apply es quien nos inicia nuestro scope, apply modificará la consulta del modelo y agregar el has('transactions') para buyer
  {
    $builder->has('transactions');
  }
}
Nota: no olvidar de importar todas la librerias 
/** Luego de esto debemos decirle al modelos de Buyer que debe de usar este scope **/
class Buyer extends User // estos modelos extenderán de User ya que un usuario puede ser vendedor o cliente
{

    protected static function boot() // construir e inicializar el modelo en este caso lo usaremos para indicar que Scope utilizar App\Scope\BuyerScope.php
    {   
        parent::boot();
        static::addGlobalScope(new BuyerScope); // le decimos que Scope usar
    }

    public function transactions() // retornará la relación de un comprador tiene muchas transacciones
    {
        return $this->hasMany(Transaction::class);
    }
}

/** Hacemos lo mismo para el modelo seller **/


/**  Soft deleting o eliminación suave **/
Es la practica de agregar una nueva tabla para que este funcione como bandera que un dato se ha eliminado, esta es una fecha
En el archivo de migraciones agragamos el campo $table->softDeletes(); 


/** Como usar Soft deleting **/
Agregamos en el modelo el uso de SoftDeletes con use Notifiable, SoftDeletes; y 
luego protected $dates = ['deleted_at'];


/** Craando un contralador desde linea de comando que use su instancia del modelo **/
esto nos servirá para hacer lo que hicimos en usuarios de usar User $user pero desde liena de comando, se inyecta la instancia
gabrielgalanmendez$ php artisan make:controller Category/CategoryController -r -m Category

/** Opereciones complejas para transactionCategory **/
Para obtener las categorías de una transaccion especifica
php artisan make:controller Transaction/TransactionCategoryController -r -m Transaction
ará uso del modelo Transaction, solo usaremos el metodo index
creamos la ruta para el controlador creado 
Route::resource('transactions.categories', 'Transaction\TransactionCategoryController', ['only' => ['index']]);
Lo que queremos es obtener las categrías de una transacción espesifica 
Lo aremos por la inyección inplicita de modelos 
class TransactionCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Transaction $transaction) // inyección inplicita del modelo
    {
        //
    }
}

Nuestras transacciones no tienen una relación directa con las categorías pero si los productos
por ello para obtener las categorías primero obtendremos los productos de las transacciones y despues de esto podremos obtener las categorías de estos productos

public function index(Transaction $transaction)
    {
        $categories = $transaction->product->categories;
        return $this->showAll($categories);
    }

// http://localhost:8000/api/transactions/1/categories esta url nos retornarán las categorías de cada transaccion pedida


/** Obtener el vendedor de una transaccion Operaciones complejas **/
php artisan make:controller Transaction/TransactionSellerController -r
Solo usaremos el metodo index, porque solo necesitamos mostrar el vendedor de esa transacción

creamos su url para el controlador 
Route::resource('transactions.sellers', 'Transaction\TransactionSellerController', ['only' => ['index']]);
accedemos al producto atravez de la transacción y de ahi al seller
public function index(Transaction $transaction)
    {
        $seller = $transaction->product->seller;
        return $this->showOne($seller);
    }
La url para esta operación es http://localhost:8000/api/transactions/2/sellers


/** Operaciones complejas con buyer **/
Obtener la lista de las transacciones de un comprador
para esto creamos este controlador Buyer/BuyerTransactionController
php artisan make:controller Buyer/BuyerTransactionController -r -m Buyer

Solo utilizaremos el metodo index


   
