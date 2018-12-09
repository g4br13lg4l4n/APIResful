<?php

namespace App\Exceptions;

use Exception;
use App\Traits\ApiResponser;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Validation\ValidationException; // importamos ValidationException

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if($exception instanceof ValidationException)
        {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }
        if($exception instanceof ModelNotFoundException)
        {
            $modelo =  strtolower(class_basename($exception->getModel())); // class_basename nos da solo el nombre del modelo
            return $this->errorResponse("No existe instancia de {$modelo} con el id especificado", 404);
        }
        if ($exception instanceof AuthenticationException)  // si fallo la autentificación
        {
            return $this->unauthenticated($request, $exception);
        }
        if ($exception instanceof AuthorizationException) // si no tiene autorización en la url
        {
            return $this->errorResponse('No tiene permisos', 403);
        }
        if ($exception instanceof NotFoundHttpException) 
        {
            return $this->errorResponse('No se encontró la url especificada', 404);
        }
        if ($exception instanceof MethodNotAllowedHttpException)
        {
            return $this->errorResponse('El metodo especificado en la petición no es vaido', 405);
        }
        if ($exception instanceof HttpException)
        {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }
        if($exception instanceof QueryException) 
        {
         $codigo = $exception->errorInfo[1]; 
         if($codigo == 1451){
            return $this->errorResponse('No se puedo eliminar porque esta relacionado con otra tabla', 409);
         }  
        }
        if(!config('app.debug')) 
        {
            return $this->errorResponse('Falla inisperada, intente luego', 500);
        }
        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->errorResponse(['No autenticado'], 401);
    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();
        return $this->errorResponse($errors, 422); // usamos el metodo errorResponse que tiene ApiResponser, al importarlo podemos usar sus funciones
    }
}
