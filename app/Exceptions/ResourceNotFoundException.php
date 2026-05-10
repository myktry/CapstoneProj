<?php

namespace App\Exceptions;

/**
 * Exception for when a resource is not found.
 * Thrown when trying to access a non-existent resource.
 */
class ResourceNotFoundException extends AppException
{
    protected string $errorCode = 'RESOURCE_NOT_FOUND';

    public function __construct(
        string $resourceType = 'Resource',
        ?string $userMessage = null,
        ?array $context = null,
    ) {
        parent::__construct(
            message: "{$resourceType} not found",
            userMessage: $userMessage ?? "The requested {$resourceType} could not be found.",
            statusCode: 404,
            errorCode: 'RESOURCE_NOT_FOUND',
            context: $context,
        );
    }
}
