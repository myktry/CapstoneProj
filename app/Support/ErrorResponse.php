<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Helper class for building error responses.
 * Provides consistent response formatting across the application.
 */
class ErrorResponse
{
    public static function json(
        string $message,
        string $errorCode = 'APP_ERROR',
        int $statusCode = 500,
        array $data = [],
        ?array $debugInfo = null,
    ): JsonResponse {
        $response = [
            'ok' => false,
            'error' => $errorCode,
            'message' => $message,
            'data' => $data ?: null,
        ];

        if (config('app.debug') && $debugInfo) {
            $response['debug'] = $debugInfo;
        }

        return response()->json(
            array_filter($response, fn ($value) => $value !== null),
            $statusCode,
        );
    }

    public static function unauthorized(string $message = 'Unauthorized', array $debugInfo = []): JsonResponse
    {
        return self::json(
            message: $message,
            errorCode: 'AUTH_ERROR',
            statusCode: 401,
            debugInfo: config('app.debug') ? $debugInfo : null,
        );
    }

    public static function forbidden(string $message = 'Forbidden', array $debugInfo = []): JsonResponse
    {
        return self::json(
            message: $message,
            errorCode: 'FORBIDDEN',
            statusCode: 403,
            debugInfo: config('app.debug') ? $debugInfo : null,
        );
    }

    public static function notFound(string $message = 'Not found', array $debugInfo = []): JsonResponse
    {
        return self::json(
            message: $message,
            errorCode: 'RESOURCE_NOT_FOUND',
            statusCode: 404,
            debugInfo: config('app.debug') ? $debugInfo : null,
        );
    }

    public static function validationError(
        string $message = 'Validation failed',
        array $errors = [],
        array $debugInfo = [],
    ): JsonResponse {
        return response()->json([
            'ok' => false,
            'error' => 'VALIDATION_ERROR',
            'message' => $message,
            'errors' => $errors,
            'debug' => config('app.debug') ? $debugInfo : null,
        ], 422);
    }

    public static function conflict(string $message = 'Conflict', array $debugInfo = []): JsonResponse
    {
        return self::json(
            message: $message,
            errorCode: 'CONFLICT',
            statusCode: 409,
            debugInfo: config('app.debug') ? $debugInfo : null,
        );
    }

    public static function rateLimited(string $message = 'Too many requests', int $retryAfter = 60): JsonResponse
    {
        return response()
            ->json([
                'ok' => false,
                'error' => 'RATE_LIMITED',
                'message' => $message,
            ], 429)
            ->header('Retry-After', $retryAfter);
    }

    public static function serverError(string $message = 'Server error', array $debugInfo = []): JsonResponse
    {
        return self::json(
            message: $message,
            errorCode: 'SERVER_ERROR',
            statusCode: 500,
            debugInfo: config('app.debug') ? $debugInfo : null,
        );
    }

    public static function serviceUnavailable(
        string $message = 'Service unavailable',
        int $retryAfter = 60,
    ): JsonResponse {
        return response()
            ->json([
                'ok' => false,
                'error' => 'SERVICE_UNAVAILABLE',
                'message' => $message,
            ], 503)
            ->header('Retry-After', $retryAfter);
    }

    public static function html(
        int $statusCode,
        string $message,
        string $errorCode,
        string $view = 'errors.app',
        array $data = [],
    ): Response {
        return response()->view($view, [
            'statusCode' => $statusCode,
            'message' => $message,
            'errorCode' => $errorCode,
            ...$data,
        ], $statusCode);
    }
}
