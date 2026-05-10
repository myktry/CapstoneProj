<?php

namespace App\Exceptions;

/**
 * Exception for refund processing errors.
 * Thrown when refund operations fail.
 */
class RefundException extends AppException
{
    protected string $errorCode = 'REFUND_ERROR';

    public function __construct(
        string $message,
        ?string $userMessage = null,
        ?array $context = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            userMessage: $userMessage ?? 'Refund request failed. Please try again or contact support.',
            statusCode: 400,
            errorCode: 'REFUND_ERROR',
            context: $context,
            previous: $previous,
        );
    }
}
