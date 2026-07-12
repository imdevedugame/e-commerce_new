<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Always return JSON for API routes, regardless of the client's Accept header.
     */
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $request->is('api/*') || parent::shouldReturnJson($request, $e);
    }

    /**
     * Give API error responses a consistent, predictable shape.
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        if (! $request->is('api/*')) {
            return parent::prepareJsonResponse($request, $e);
        }

        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($e instanceof ModelNotFoundException
            || ($e instanceof NotFoundHttpException && $e->getPrevious() instanceof ModelNotFoundException)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        return response()->json([
            'message' => $status === 500 && ! config('app.debug')
                ? 'Server error.'
                : $e->getMessage(),
        ], $status);
    }
}
