<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

/**
 * Base exception class for application errors.
 * Provides consistent error handling and user-friendly messages.
 */
class AppException extends Exception
{
    protected ?string $userMessage = null;
    protected int $httpStatusCode = 500;
    protected ?array $context = null;
    protected string $errorCode = 'APP_ERROR';

    public function __construct(
        string $message,
        ?string $userMessage = null,
        int $statusCode = 500,
        string $errorCode = 'APP_ERROR',
        ?array $context = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
        
        $this->userMessage = $userMessage ?? 'An error occurred. Please try again.';
        $this->httpStatusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function toResponse($request)
    {
        if ($request->expectsJson()) {
            return $this->jsonResponse();
        }

        return $this->htmlResponse();
    }

    protected function jsonResponse(): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'error' => $this->errorCode,
            'message' => $this->userMessage,
            'debug' => config('app.debug') ? [
                'exception' => class_basename($this),
                'details' => $this->message,
            ] : null,
        ], $this->httpStatusCode);
    }

    protected function htmlResponse(): Response
    {
        return response()->view('errors.app', [
            'message' => $this->userMessage,
            'statusCode' => $this->httpStatusCode,
            'errorCode' => $this->errorCode,
        ], $this->httpStatusCode);
    }
}
