<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Str;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.token' => \App\Http\Middleware\AuthTokenMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $traceId = (string) Str::uuid();

                // Validation Errors (422)
                if ($e instanceof ValidationException) {
                    $details = [];
                    foreach ($e->errors() as $field => $messages) {
                        $details[] = ['field' => $field, 'message' => $messages[0]];
                    }
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'VALIDATION_ERROR',
                            'message' => 'Hay campos con errores.',
                            'details' => $details,
                            'trace_id' => $traceId
                        ]
                    ], 422);
                }

                // Not Found (404)
                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'NOT_FOUND',
                            'message' => 'El recurso solicitado no existe.',
                            'trace_id' => $traceId
                        ]
                    ], 404);
                }

                // Default Internal Error (500) - Sin trazas técnicas si APP_DEBUG=false
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INTERNAL_ERROR',
                        'message' => 'Ocurrió un error interno en el servidor.',
                        'trace_id' => $traceId
                    ]
                ], 500);
            }
        });
    })->create();