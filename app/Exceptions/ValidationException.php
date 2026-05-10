<?php

namespace App\Exceptions;

/**
 * Exception for validation errors.
 * Thrown when request validation fails.
 */
class ValidationException extends AppException
{
    protected string $errorCode = 'VALIDATION_ERROR';

    protected array $errors = [];

    public function __construct(
        string $message,
        array $errors = [],
        ?string $userMessage = null,
        ?array $context = null,
    ) {
        parent::__construct(
            message: $message,
            userMessage: $userMessage ?? 'Validation failed. Please check your input.',
            statusCode: 422,
            errorCode: 'VALIDATION_ERROR',
            context: $context,
        );

        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function jsonResponse()
    {
        $response = parent::jsonResponse();

        return response()->json(
            array_merge($response->getData(true), [
                'errors' => $this->errors,
            ]),
            $this->httpStatusCode,
        );
    }
}
