# Error Handling Quick Reference

## Common Patterns

### Authentication Required
```php
use App\Exceptions\AuthenticationException;

if (!$request->user()) {
    throw new AuthenticationException(
        'User not authenticated',
        'You must be logged in to perform this action.'
    );
}
```

### Authorization Check
```php
use App\Exceptions\AuthorizationException;

if ($resource->user_id !== $request->user()->id) {
    throw new AuthorizationException(
        'User does not own this resource',
        'You do not have permission to access this resource.'
    );
}
```

### Resource Not Found
```php
use App\Exceptions\ResourceNotFoundException;

$resource = Resource::find($id);
if (!$resource) {
    throw new ResourceNotFoundException(
        'Resource',
        'The requested resource could not be found.'
    );
}
```

### Validation Error
```php
use App\Exceptions\ValidationException;

try {
    $validated = $request->validate([...]);
} catch (\Illuminate\Validation\ValidationException $e) {
    throw new ValidationException(
        'Validation failed',
        $e->errors(),
        'Please check your input and try again.'
    );
}
```

### Payment Error
```php
use App\Exceptions\PaymentException;

try {
    // Stripe operation
} catch (\Throwable $e) {
    Log::error('Payment failed', ['error' => $e->getMessage()]);
    throw new PaymentException(
        'Stripe operation failed: ' . $e->getMessage(),
        'Payment processing failed. Please try again.',
        context: ['service_id' => $serviceId],
        previous: $e
    );
}
```

### OTP Error
```php
use App\Exceptions\OtpException;

try {
    // OTP operation
} catch (\Throwable $e) {
    throw new OtpException(
        'OTP delivery failed: ' . $e->getMessage(),
        'We could not send your verification code. Please try again.',
        previous: $e
    );
}
```

### Rate Limiting
```php
use App\Exceptions\OtpException;

if ($recentAttempts > $limit) {
    throw OtpException::rateLimited(
        'Please wait 60 seconds before trying again.'
    );
}
```

### Security Error
```php
use App\Exceptions\SecurityException;

if ($resolvedPath !== realpath($path)) {
    throw new SecurityException(
        'Path traversal attempt detected',
        'Security validation failed.'
    );
}
```

### Service Unavailable
```php
use App\Exceptions\ServiceUnavailableException;

try {
    // External API call
} catch (\Throwable $e) {
    throw new ServiceUnavailableException(
        'Stripe',
        'Stripe is temporarily unavailable. Please try again later.',
        previous: $e
    );
}
```

## Error Codes Reference

| Code | Status | Use Case |
|------|--------|----------|
| VALIDATION_ERROR | 422 | Form validation |
| AUTH_ERROR | 401 | Not authenticated |
| FORBIDDEN | 403 | No permission |
| RESOURCE_NOT_FOUND | 404 | Resource doesn't exist |
| PAYMENT_ERROR | 402 | Payment failed |
| REFUND_ERROR | 400 | Refund failed |
| OTP_ERROR | 400 | OTP failed |
| RATE_LIMITED | 429 | Too many requests |
| SERVICE_UNAVAILABLE | 503 | External service down |
| SECURITY_ERROR | 403 | Security violation |

## Logging Best Practices

```php
// Error (use for all exceptions)
Log::error('Operation failed', [
    'user_id' => $user->id,
    'resource_id' => $resource->id,
    'error' => $exception->getMessage(),
]);

// Warning (use for expected failures)
Log::warning('Refund not available', [
    'appointment_id' => $appointmentId,
    'reason' => 'Too close to appointment time',
]);

// Info (use for important events)
Log::info('Payment processed successfully', [
    'user_id' => $user->id,
    'amount' => $amount,
    'session_id' => $sessionId,
]);

// Alert (use for security events)
Log::alert('Security violation detected', [
    'admin_id' => $adminId,
    'event' => 'path_traversal_attempt',
    'ip_address' => $ip,
]);
```

## Response Handling

### For JSON Requests
Exceptions automatically return JSON:
```json
{
  "ok": false,
  "error": "VALIDATION_ERROR",
  "message": "Validation failed",
  "errors": {"email": ["Email is required"]},
  "debug": {} // only in debug mode
}
```

### For HTML Requests
Exceptions render views:
- `errors/app.blade.php` for custom exceptions
- `errors/500.blade.php` for server errors

## Testing Error Handling

```php
public function test_authentication_required()
{
    $this->actingAs(null)
        ->post('/api/endpoint')
        ->assertStatus(401)
        ->assertJson(['error' => 'AUTH_ERROR']);
}

public function test_validation_error()
{
    $this->post('/api/endpoint', [])
        ->assertStatus(422)
        ->assertJson(['error' => 'VALIDATION_ERROR']);
}

public function test_authorization_denied()
{
    $this->actingAs($user)
        ->post('/api/endpoint/' . $otherUserResource->id)
        ->assertStatus(403)
        ->assertJson(['error' => 'FORBIDDEN']);
}
```

## Adding New Exception Types

1. Create exception class in `app/Exceptions/`
2. Extend `AppException`
3. Set appropriate `errorCode` and `httpStatusCode`
4. Add to documentation
5. Use in controllers/services

Example:
```php
namespace App\Exceptions;

class CustomException extends AppException
{
    protected string $errorCode = 'CUSTOM_ERROR';

    public function __construct(
        string $message,
        ?string $userMessage = null,
        ?array $context = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            userMessage: $userMessage ?? 'A custom error occurred.',
            statusCode: 400,
            errorCode: 'CUSTOM_ERROR',
            context: $context,
            previous: $previous,
        );
    }
}
```
