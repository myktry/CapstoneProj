<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReceiptDecryptionController extends Controller
{
    public function decrypt(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $payload = $request->validate([
            'transaction_id' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/'],
            'receipt_file' => ['required', 'string', 'max:160', 'regex:/^[A-Za-z0-9._-]+\.(jpg|jpeg|png|webp)$/i'],
        ]);

        $adminId = $request->user()?->id;
        $ip = $request->ip();
        $transactionId = $payload['transaction_id'];

        $this->audit(
            adminId: $adminId,
            ipAddress: $ip,
            transactionId: $transactionId,
            event: 'decryption_attempt',
            status: 'success',
            message: 'Receipt decryption function called.'
        );

        $file = $payload['receipt_file'];

        if (! Str::startsWith($file, $transactionId)) {
            return $this->fail($adminId, $ip, $transactionId, 'Receipt filename must start with transaction ID.');
        }

        $baseDir = storage_path('app/receipts');
        $baseDirReal = realpath($baseDir);

        if ($baseDirReal === false) {
            return $this->fail($adminId, $ip, $transactionId, 'Receipts directory is not available.');
        }

        $targetPath = $baseDirReal . DIRECTORY_SEPARATOR . basename($file);
        $resolvedPath = realpath($targetPath);

        if ($resolvedPath === false || ! str_starts_with($resolvedPath, $baseDirReal . DIRECTORY_SEPARATOR)) {
            return $this->fail($adminId, $ip, $transactionId, 'Path traversal attempt or invalid receipt path detected.');
        }

        if (! is_file($resolvedPath) || ! is_readable($resolvedPath)) {
            return $this->fail($adminId, $ip, $transactionId, 'Receipt file does not exist or is not readable.');
        }

        $mime = mime_content_type($resolvedPath) ?: '';
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

        if (! in_array($mime, $allowedMimes, true) || getimagesize($resolvedPath) === false) {
            return $this->fail($adminId, $ip, $transactionId, 'Invalid or unsupported image format.');
        }

        $checksumPath = $resolvedPath . '.sha256';

        if (! is_file($checksumPath) || ! is_readable($checksumPath)) {
            return $this->fail($adminId, $ip, $transactionId, 'Missing checksum file for integrity verification.');
        }

        $expectedChecksum = trim((string) file_get_contents($checksumPath));
        $expectedChecksum = strtolower(substr($expectedChecksum, 0, 64));
        $actualChecksum = hash_file('sha256', $resolvedPath);

        if (! preg_match('/^[a-f0-9]{64}$/', $expectedChecksum) || ! hash_equals($expectedChecksum, $actualChecksum)) {
            return $this->fail($adminId, $ip, $transactionId, 'Integrity check failed: receipt appears tampered.');
        }

        $this->audit(
            adminId: $adminId,
            ipAddress: $ip,
            transactionId: $transactionId,
            event: 'decryption_success',
            status: 'success',
            message: 'Receipt passed validation and integrity checks.',
            context: [
                'receipt_file' => basename($resolvedPath),
                'mime_type' => $mime,
                'sha256' => $actualChecksum,
            ]
        );

        return response()->json([
            'ok' => true,
            'message' => 'Receipt validated and ready for secure processing.',
            'transaction_id' => $transactionId,
            'receipt_file' => basename($resolvedPath),
        ]);
    }

    private function fail(?int $adminId, ?string $ipAddress, ?string $transactionId, string $reason): JsonResponse
    {
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

        return response()->json([
            'ok' => false,
            'message' => $reason,
        ], 422);
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
