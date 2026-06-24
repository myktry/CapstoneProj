<?php

namespace App\Exceptions;

/**
 * Exception for security-related errors.
 * Thrown when security checks fail (path traversal, invalid signatures, etc.).
 */
class SecurityException extends AppException
{
    protected string $errorCode = 'SECURITY_ERROR';

    public function __construct(
        string $message,
        ?string $userMessage = null,
        ?array $context = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            userMessage: $userMessage ?? 'A security check failed. Please try again.',
            statusCode: 403,
            errorCode: 'SECURITY_ERROR',
            context: $context,
            previous: $previous,
        );
    }
}
