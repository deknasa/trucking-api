<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Server\Exception\OAuthServerException;
use PDOException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        OAuthServerException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            $user = auth('api')->user();
            $userName = null;

            if ($user) $userName = $user->name;

            do {
                Log::error("\nuser = " . $userName . "\nerror = [" . $e->getCode() . '] "' . $e->getMessage() . '" on line ' . $e->getTrace()[0]['line'] . ' of file ' . $e->getTrace()[0]['file'] . "\nrequest = " . request()->__toString() . "\n");
            } while ($e = $e->getPrevious());
        });
    }

    // public function render($request, Throwable $e)
    // {
    //     if (
    //         $e instanceof QueryException ||
    //         $e instanceof PDOException
    //     ) {
    //         $errors = [];

    //         do {
    //             $errors[] = $e->getMessage();
    //         } while ($e = $e->getPrevious());

    //         return response()->json([
    //             'message' => 'Internal server error.',
    //             'errors' => $errors,
    //         ], 500);
    //     }

    //     return parent::render($request, $e);
    // }
}
