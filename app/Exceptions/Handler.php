<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

     * @param \Exception $exception

     *

     * @throws \Exception

     *

     * @return void

     */
    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    /**

     * Render an exception into an HTTP response.

     *

     * @param \Illuminate\Http\Request $request

     * @param \Exception               $exception

     *

     * @throws \Exception

     *

     * @return \Symfony\Component\HttpFoundation\Response

     */
    public function render($request, Throwable $e)
    {
        if ($this->isHttpException($e)) {
            $statusCode = $e->getStatusCode();

            switch ($statusCode) {

                case '400':

                    return redirect()->back()->withInput();

                    break;

            }
        }

        if ($e instanceof PostTooLargeException) {
            return response()->json([

                'message' => 'File too large!',

                'code'    => 422,

            ]);
        }

        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($e instanceof \Illuminate\Session\TokenMismatchException) {
            abort(400); /* bad request */
        }

        return parent::render($request, $e);
    }
}
