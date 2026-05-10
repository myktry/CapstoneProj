<?php

namespace App\Exceptions;

/**
 * Exception for external service failures.
 * Thrown when third-party services (Stripe, SMS, etc.) are unavailable.
 */
class ServiceUnavailableException extends AppException
{
    protected string $errorCode = 'SERVICE_UNAVAILABLE';

    public function __construct(
        string $serviceName = 'Service',
        ?string $userMessage = null,
        ?array $context = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            message: "{$serviceName} is currently unavailable",
            userMessage: $userMessage ?? 'A required service is temporarily unavailable. Please try again later.',
            statusCode: 503,
            errorCode: 'SERVICE_UNAVAILABLE',
            context: $context,
            previous: $previous,
        );
    }
}
