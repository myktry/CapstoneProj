<?php

namespace App\Exceptions;

/**
 * Exception for OTP (One-Time Password) operations.
 * Thrown when OTP delivery, verification, or validation fails.
 */
class OtpException extends AppException
{
    protected string $errorCode = 'OTP_ERROR';

    public function __construct(
        string $message,
        ?string $userMessage = null,
        ?array $context = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            userMessage: $userMessage ?? 'OTP operation failed. Please try again.',
            statusCode: 400,
            errorCode: 'OTP_ERROR',
            context: $context,
            previous: $previous,
        );
    }

    public static function rateLimited(string $message): self
    {
        return new self(
            message: $message,
            userMessage: $message,
            statusCode: 429,
        );
    }
}
