<?php

namespace App\Exceptions;

/**
 * Exception for authorization failures.
 * Thrown when user is authenticated but lacks required permissions.
 */
class AuthorizationException extends AppException
{
    protected string $errorCode = 'FORBIDDEN';

    public function __construct(
        string $message = 'Unauthorized access',
        ?string $userMessage = null,
        ?array $context = null,
    ) {
        parent::__construct(
            message: $message,
            userMessage: $userMessage ?? 'You do not have permission to perform this action.',
            statusCode: 403,
            errorCode: 'FORBIDDEN',
            context: $context,
        );
    }
}
