<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'livewire/upload*',
            'livewire/upload-file*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
            'new_password',
            'new_password_confirmation',
            'otp',
            'token',
            'secret',
            'api_key',
            'access_token',
        ]);

        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e): bool {
            return $request->is('livewire/*')
                || $request->hasHeader('X-Livewire')
                || $request->expectsJson();
        });

        // Handle custom application exceptions
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof \App\Exceptions\AppException) {
                return $e->toResponse($request);
            }

            // Handle HTTP exceptions
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $statusCode = $e->getStatusCode();
                $message = match ($statusCode) {
                    404 => 'The requested resource was not found.',
                    403 => 'You do not have permission to access this resource.',
                    401 => 'You must be authenticated to access this resource.',
                    429 => 'Too many requests. Please try again later.',
                    503 => 'Service temporarily unavailable. Please try again later.',
                    default => $e->getMessage(),
                };

                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'error' => match ($statusCode) {
                            404 => 'RESOURCE_NOT_FOUND',
                            403 => 'FORBIDDEN',
                            401 => 'AUTH_ERROR',
                            429 => 'RATE_LIMITED',
                            503 => 'SERVICE_UNAVAILABLE',
                            default => 'HTTP_ERROR',
                        },
                        'message' => $message,
                    ], $statusCode);
                }

                return response()->view('errors.app', [
                    'statusCode' => $statusCode,
                    'message' => $message,
                    'errorCode' => match ($statusCode) {
                        404 => 'RESOURCE_NOT_FOUND',
                        403 => 'FORBIDDEN',
                        401 => 'AUTH_ERROR',
                        429 => 'RATE_LIMITED',
                        503 => 'SERVICE_UNAVAILABLE',
                        default => 'HTTP_ERROR',
                    },
                ], $statusCode);
            }

            // Handle validation exceptions
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'VALIDATION_ERROR',
                        'message' => 'Validation failed',
                        'errors' => $e->errors(),
                    ], 422);
                }
            }

            return null;
        });

        $exceptions->respond(function (Response $response, Throwable $e, Request $request): Response {
            if ($response->getStatusCode() === 500 && ! $request->expectsJson()) {
                return response()->view('errors.500', [], 500);
            }

            return $response;
        });
    })->create();
