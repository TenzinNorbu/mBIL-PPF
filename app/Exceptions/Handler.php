<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

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

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->view('errors.404', [], 404);
        }elseif ($exception instanceof \Illuminate\Http\Exceptions\PostTooLargeException) {
            return response()->json([
                'message' => 'The request entity is too large.',
            ], 413);
        } elseif ($exception instanceof \Illuminate\Http\Exceptions\UnsupportedMediaTypeHttpException) {
            return response()->json([
                'message' => 'The content type of the request is not supported.',
            ], 415);
        }elseif ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['message' => 'Page not found'], 404);
        }
        elseif ($exception instanceof HttpException && $exception->getStatusCode() == 403) {
            return response()->view('errors.403', [], Response::HTTP_FORBIDDEN);
        }
        return parent::render($request, $exception);
    }

    protected function prepareResponse($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }
        return parent::prepareResponse($request, $e);
    }


    // public function render($request, Exception $exception)
    // {
    //     if ($exception instanceof ModelNotFoundException) {
    //         return response()->json(['error' => 'Resource not found'], 404);
    //     }

    //     return parent::render($request, $exception);
    // }
    
}
