<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AuthorizationException;
use App\Exceptions\SecurityException;
use App\Exceptions\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\ReceiptRecord;
use App\Models\SecurityAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReceiptDecryptionController extends Controller
{
    public function decrypt(Request $request): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            throw new AuthorizationException(
                'User is not an admin',
                'You do not have permission to access this resource.',
            );
        }

        try {
            $payload = $request->validate([
                'transaction_id' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/'],
                'receipt_file' => ['nullable', 'string', 'max:160', 'regex:/^[A-Za-z0-9._-]+\.(jpg|jpeg|png|webp)$/i'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new ValidationException(
                'Receipt decryption validation failed',
                $e->errors(),
                'Invalid request parameters.',
            );
        }

        $adminId = $request->user()?->id;
        $ip = $request->ip();
        $transactionId = $payload['transaction_id'];

        try {
            $this->audit(
                adminId: $adminId,
                ipAddress: $ip,
                transactionId: $transactionId,
                event: 'decryption_attempt',
                status: 'success',
                message: 'Receipt decryption function called.'
            );
        } catch (\Throwable $exception) {
            Log::error('Failed to create audit log', [
                'error' => $exception->getMessage(),
            ]);
        }

        $record = ReceiptRecord::query()
            ->where('transaction_id', $transactionId)
            ->where('is_active', true)
            ->first();

        if (! $record) {
            $this->auditFailure($adminId, $ip, $transactionId, 'No active receipt record found for transaction.');
            throw new SecurityException(
                'No active receipt record found',
                'The requested receipt record does not exist or is inactive.',
            );
        }

        if (filled($payload['receipt_file'] ?? null) && $payload['receipt_file'] !== $record->filename) {
            $this->auditFailure($adminId, $ip, $transactionId, 'Provided receipt filename does not match the bound receipt record.');
            throw new SecurityException(
                'Receipt filename mismatch',
                'The provided filename does not match our records.',
            );
        }

        try {
            $baseDir = storage_path('app/receipts');
            if (! is_dir($baseDir)) {
                @mkdir($baseDir, 0750, true);
            }

            $baseDirReal = realpath($baseDir);

            if ($baseDirReal === false) {
                throw new \RuntimeException('Receipts directory cannot be resolved');
            }

            if (str_starts_with($baseDirReal, public_path())) {
                throw new \RuntimeException('Receipts directory must not be web-accessible');
            }

            if (is_link($baseDirReal)) {
                throw new \RuntimeException('Receipts directory symlink is not allowed');
            }

            $targetPath = storage_path('app/' . ltrim($record->relative_path, '/\\'));
            $resolvedPath = realpath($targetPath);

            if ($resolvedPath === false || ! str_starts_with($resolvedPath, $baseDirReal . DIRECTORY_SEPARATOR)) {
                throw new \RuntimeException('Path traversal attempt or invalid receipt path detected');
            }

            if (basename($resolvedPath) !== $record->filename) {
                throw new \RuntimeException('Receipt filename mismatch with receipt record binding');
            }

            if (! is_file($resolvedPath) || ! is_readable($resolvedPath)) {
                throw new \RuntimeException('Receipt file does not exist or is not readable');
            }

            if (PHP_OS_FAMILY !== 'Windows') {
                $dirPerms = fileperms($baseDirReal);
                $filePerms = fileperms($resolvedPath);

                if (($dirPerms !== false && ($dirPerms & 0x0002) === 0x0002) || ($filePerms !== false && ($filePerms & 0x0002) === 0x0002)) {
                    throw new \RuntimeException('Receipt directory/file permissions are too permissive');
                }

                @chmod($baseDirReal, 0750);
                @chmod($resolvedPath, 0640);
            }

            $mime = mime_content_type($resolvedPath) ?: '';
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

            if (! in_array($mime, $allowedMimes, true) || getimagesize($resolvedPath) === false) {
                throw new \RuntimeException('Invalid or unsupported image format');
            }

            if ($record->mime_type !== $mime) {
                throw new \RuntimeException('MIME type does not match receipt record');
            }

            if (! preg_match('/^[a-f0-9]{64}$/', (string) $record->sha256_hash)) {
                throw new \RuntimeException('Receipt record hash is invalid');
            }

            $expectedChecksum = strtolower($record->sha256_hash);
            $actualChecksum = hash_file('sha256', $resolvedPath);

            if (! hash_equals($expectedChecksum, $actualChecksum)) {
                throw new \RuntimeException('Integrity check failed: receipt appears tampered');
            }

            if (filled($record->size_bytes) && (int) $record->size_bytes !== (int) filesize($resolvedPath)) {
                throw new \RuntimeException('File size mismatch with receipt record');
            }
        } catch (\RuntimeException $exception) {
            $this->auditFailure($adminId, $ip, $transactionId, $exception->getMessage());
            throw new SecurityException(
                'Security validation failed: ' . $exception->getMessage(),
                'Receipt security validation failed. Access denied.',
            );
        } catch (\Throwable $exception) {
            Log::error('Unexpected error during receipt decryption', [
                'transaction_id' => $transactionId,
                'error' => $exception->getMessage(),
            ]);
            $this->auditFailure($adminId, $ip, $transactionId, 'Unexpected error: ' . $exception->getMessage());
            throw new SecurityException(
                'Unexpected error during receipt validation',
                'An error occurred while processing your request.',
            );
        }

        try {
            $this->audit(
                adminId: $adminId,
                ipAddress: $ip,
                transactionId: $transactionId,
                event: 'decryption_success',
                status: 'success',
                message: 'Receipt passed validation and integrity checks.',
                context: [
                    'receipt_file' => $record->filename,
                    'relative_path' => $record->relative_path,
                    'mime_type' => $mime,
                    'sha256' => $actualChecksum,
                ]
            );
        } catch (\Throwable $exception) {
            Log::error('Failed to create success audit log', [
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Receipt validated and ready for secure processing.',
            'transaction_id' => $transactionId,
            'receipt_file' => $record->filename,
        ]);
    }

    private function auditFailure(?int $adminId, ?string $ipAddress, ?string $transactionId, string $reason): void
    {
        try {
            $this->audit(
                adminId: $adminId,
                ipAddress: $ipAddress,
                transactionId: $transactionId,
                event: 'decryption_failed',
                status: 'failed',
                message: $reason
            );

            $this->audit(
                adminId: $adminId,
                ipAddress: $ipAddress,
                transactionId: $transactionId,
                event: 'security_alert',
                status: 'failed',
                message: 'Security alert triggered during receipt decryption.'
            );

            Log::alert('Security alert triggered during receipt decryption.', [
                'admin_id' => $adminId,
                'ip_address' => $ipAddress,
                'transaction_id' => $transactionId,
                'reason' => $reason,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to create audit log for receipt decryption failure', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function audit(
        ?int $adminId,
        ?string $ipAddress,
        ?string $transactionId,
        string $event,
        string $status,
        ?string $message = null,
        ?array $context = null
    ): void {
        SecurityAuditLog::query()->create([
            'admin_id' => $adminId,
            'event' => $event,
            'status' => $status,
            'ip_address' => $ipAddress,
            'transaction_id' => $transactionId,
            'message' => $message,
            'context' => $context,
        ]);
    }
}
