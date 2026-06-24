<?php

namespace App\Exceptions;

/**
 * Exception for authentication failures.
 * Thrown when user is not authenticated or session is invalid.
 */
class AuthenticationException extends AppException
{
    protected string $errorCode = 'AUTH_ERROR';

    public function __construct(
        string $message = 'Authentication failed',
        ?string $userMessage = null,
        ?array $context = null,
    ) {
        parent::__construct(
            message: $message,
            userMessage: $userMessage ?? 'You must be logged in to continue.',
            statusCode: 401,
            errorCode: 'AUTH_ERROR',
            context: $context,
        );
    }
}
