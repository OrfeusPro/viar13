<?php

namespace App\Exceptions;

use Exception;
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

     * @param \Exception $exception

     *

     * @throws \Exception

     *

     * @return void

     */
    public function report(Exception $exception)
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
    public function render($request, Exception $e)
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
