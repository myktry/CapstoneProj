<?php

namespace App\Exceptions;

/**
 * Exception for payment processing errors.
 * Thrown when Stripe or other payment operations fail.
 */
class PaymentException extends AppException
{
    protected string $errorCode = 'PAYMENT_ERROR';

    public function __construct(
        string $message,
        ?string $userMessage = null,
        ?array $context = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            userMessage: $userMessage ?? 'Payment processing failed. Please try again or contact support.',
            statusCode: 402,
            errorCode: 'PAYMENT_ERROR',
            context: $context,
            previous: $previous,
        );
    }
}
