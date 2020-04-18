<?php

namespace App\Exceptions;

use Exception;
use http\Env\Response;
use Illuminate\Auth\AuthenticationException;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,

        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
            $response['message'] = $error;
        }


        return response()->json($response, $code);
    }

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


            if($exception instanceof ModelNotFoundException)
            {

                return $this->sendError('#550', "Server Internal Error : ModelNotFoundException");
            }

            if($exception instanceof NotFoundHttpException )
            {

               return $this->sendError('#550', "Server Internal Error : NotFoundHttpException");
            }
            if($exception instanceof InvalidArgumentException )
            {
            //    return Response()->json('fffffffffff',404);
               return $this->sendError('#550', "Server Internal Error : InvalidArgumentException ");
            }
        if($exception instanceof AuthenticationException ) {

            return $this->sendError('#204', "Server Internal Error :Unauthenticated");
        }
        if ($exception){
            // log the error

            return $this->sendError('#550', "Server Internal Error :".$exception->getMessage());

        }
        return parent::render($request, $exception);
    }
}
