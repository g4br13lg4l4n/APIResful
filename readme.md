## Api Laravel
By Gabriel Galán Méndez

## Notas
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


