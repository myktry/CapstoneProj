# Error Handling Implementation Guide

## Overview

This document outlines the comprehensive error handling system implemented across the Black Ember application.

## Architecture

### 1. Exception Hierarchy

The application uses a custom exception hierarchy rooted in `AppException`:

```
AppException (base class)
├── ValidationException
├── AuthenticationException
├── AuthorizationException
├── ResourceNotFoundException
├── PaymentException
├── RefundException
├── OtpException
├── ServiceUnavailableException
└── SecurityException
```

### 2. Exception Classes

All custom exceptions are located in `app/Exceptions/`.

#### AppException
- Base exception class providing consistent error handling
- Provides user-friendly messages separate from technical details
- Automatically handles JSON and HTML responses based on request type
- Properties:
  - `userMessage`: User-facing message
  - `httpStatusCode`: HTTP status code
  - `errorCode`: Machine-readable error code
  - `context`: Additional debugging context

#### ValidationException
- Status Code: 422
- Used for request validation failures
- Includes structured validation errors

#### AuthenticationException
- Status Code: 401
- Used when user is not authenticated

#### AuthorizationException
- Status Code: 403
- Used when user lacks required permissions

#### ResourceNotFoundException
- Status Code: 404
- Used when a requested resource doesn't exist

#### PaymentException
- Status Code: 402
- Used for Stripe/payment processing errors

#### RefundException
- Status Code: 400
- Used for refund operation failures

#### OtpException
- Status Code: 400 (or 429 for rate limiting)
- Used for OTP delivery and verification failures
- Supports rate limiting with custom status code

#### ServiceUnavailableException
- Status Code: 503
- Used when third-party services are unavailable

#### SecurityException
- Status Code: 403
- Used for security validation failures (path traversal, tampering, etc.)

## Usage Examples

### Throwing an Exception

```php
// Basic usage
throw new PaymentException(
    'Failed to create Stripe session',
    'Could not create checkout session. Please try again.',
    context: ['service_id' => $serviceId],
    previous: $originalException
);

// With authentication error
throw new AuthenticationException(
    'User not authenticated',
    'You must be logged in to perform this action.'
);

// OTP rate limiting
throw OtpException::rateLimited('Please wait 30 seconds before requesting a new code.');
```

### Error Response Handling

The `ErrorResponse` helper class provides convenient methods for creating error responses:

```php
use App\Support\ErrorResponse;

// JSON responses
ErrorResponse::json('Error message', 'ERROR_CODE', 500);
ErrorResponse::unauthorized('Unauthorized access');
ErrorResponse::forbidden('Forbidden access');
ErrorResponse::notFound('Resource not found');
ErrorResponse::validationError('Validation failed', $errors);
ErrorResponse::rateLimited('Too many requests', 60);
ErrorResponse::serverError('Server error');
ErrorResponse::serviceUnavailable('Service unavailable', 60);
```

## Controllers with Error Handling

### CheckoutController
- Handles Stripe checkout session creation
- Throws `ResourceNotFoundException` if booking data missing
- Throws `PaymentException` if Stripe API fails
- Logs detailed error information

### BookingRefundController
- Throws `AuthorizationException` for unauthorized access
- Throws `RefundException` for various refund failures
- Validates appointment status and refund eligibility
- Handles Stripe API errors gracefully

### BookingNotificationController
- Throws `AuthenticationException` if user not logged in
- Throws `AuthorizationException` if notification doesn't belong to user
- Includes error logging for database operations

### StripeWebhookController
- Validates webhook signatures with error handling
- Logs webhook events and failures
- Catches errors during appointment updates
- Gracefully handles mail and notification failures

### ReceiptDecryptionController
- Validates admin privileges with `AuthorizationException`
- Throws `SecurityException` for security validation failures
- Comprehensive security audit logging
- Path traversal and file tampering detection

## Services with Error Handling

### OtpService
- Issues OTP codes with rate limiting
- Throws `OtpException` for delivery failures
- Separates email and SMS delivery errors
- Logs all OTP operations

### RefundStatusSyncService
- Syncs refund status with Stripe
- Catches Stripe API errors gracefully
- Logs mail and notification failures
- Returns existing status on error

### DeliverOtpCode (Job)
- Delivers OTP via email or SMS
- Throws exceptions on delivery failure
- Logs successful and failed deliveries
- Supports job retry logic

## Bootstrap Exception Handler

The exception handler in `bootstrap/app.php` provides:

1. **Custom Exception Rendering**
   - Routes `AppException` instances to their own response handlers
   - Handles HTTP exceptions with appropriate status codes
   - Converts Laravel validation exceptions to structured format

2. **Response Format**
   - JSON for API requests
   - HTML views for browser requests
   - Consistent error response structure

3. **Error Response Structure**
   ```json
   {
     "ok": false,
     "error": "ERROR_CODE",
     "message": "User-friendly message",
     "errors": {},
     "debug": {} // Only in debug mode
   }
   ```

## Error Views

### `resources/views/errors/app.blade.php`
- Generic error view for custom application exceptions
- Displays error code, message, and action buttons
- Shows additional debug info in debug mode

### `resources/views/errors/500.blade.php`
- Generic 500 error page
- User-friendly message

## Logging

All errors are logged to `storage/logs/laravel.log` with:
- Error message and exception class
- Stack trace (in debug mode)
- Context data (user ID, request data, etc.)
- Severity level (error, warning, alert)

## Error Codes

| Error Code | Status | Description |
|-----------|--------|-------------|
| VALIDATION_ERROR | 422 | Request validation failed |
| AUTH_ERROR | 401 | Authentication required |
| FORBIDDEN | 403 | Authorization denied |
| RESOURCE_NOT_FOUND | 404 | Resource not found |
| PAYMENT_ERROR | 402 | Payment processing failed |
| REFUND_ERROR | 400 | Refund operation failed |
| OTP_ERROR | 400 | OTP operation failed |
| SERVICE_UNAVAILABLE | 503 | Service temporarily unavailable |
| SECURITY_ERROR | 403 | Security validation failed |
| RATE_LIMITED | 429 | Too many requests |
| CONFLICT | 409 | Resource conflict |
| SERVER_ERROR | 500 | Internal server error |

## Best Practices

1. **Always Include User Messages**
   - Never expose technical details to users
   - Provide helpful, actionable messages

2. **Include Context**
   - Pass context data for debugging
   - Include relevant identifiers in logs

3. **Log Appropriately**
   - Use `Log::error()` for errors
   - Use `Log::warning()` for warnings
   - Use `Log::info()` for important events

4. **Handle External APIs**
   - Wrap third-party calls in try-catch
   - Provide fallback behavior
   - Log API failures with details

5. **Validation**
   - Validate early
   - Throw `ValidationException` with detailed errors
   - Include field-level error information

6. **Security**
   - Use `SecurityException` for security violations
   - Audit security events
   - Never expose sensitive information in errors

## Future Enhancements

1. Error tracking integration (Sentry, Rollbar)
2. Error analytics dashboard
3. Automated error notifications
4. Error recovery mechanisms
5. Rate limiting middleware
6. Circuit breaker pattern for external services
