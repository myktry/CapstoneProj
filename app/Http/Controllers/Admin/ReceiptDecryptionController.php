<?php

namespace App\Http\Controllers\Admin;

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
        abort_unless($request->user()?->isAdmin(), 403);

        $payload = $request->validate([
            'transaction_id' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/'],
            'receipt_file' => ['nullable', 'string', 'max:160', 'regex:/^[A-Za-z0-9._-]+\.(jpg|jpeg|png|webp)$/i'],
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

        $record = ReceiptRecord::query()
            ->where('transaction_id', $transactionId)
            ->where('is_active', true)
            ->first();

        if (! $record) {
            return $this->fail($adminId, $ip, $transactionId, 'No active receipt record found for transaction.');
        }

        if (filled($payload['receipt_file'] ?? null) && $payload['receipt_file'] !== $record->filename) {
            return $this->fail($adminId, $ip, $transactionId, 'Provided receipt filename does not match the bound receipt record.');
        }

        $baseDir = storage_path('app/receipts');
        if (! is_dir($baseDir)) {
            @mkdir($baseDir, 0750, true);
        }

        $baseDirReal = realpath($baseDir);

        if ($baseDirReal === false) {
            return $this->fail($adminId, $ip, $transactionId, 'Receipts directory is not available.');
        }

        if (str_starts_with($baseDirReal, public_path())) {
            return $this->fail($adminId, $ip, $transactionId, 'Receipts directory must not be web-accessible.');
        }

        if (is_link($baseDirReal)) {
            return $this->fail($adminId, $ip, $transactionId, 'Receipts directory symlink is not allowed.');
        }

        $targetPath = storage_path('app/' . ltrim($record->relative_path, '/\\'));
        $resolvedPath = realpath($targetPath);

        if ($resolvedPath === false || ! str_starts_with($resolvedPath, $baseDirReal . DIRECTORY_SEPARATOR)) {
            return $this->fail($adminId, $ip, $transactionId, 'Path traversal attempt or invalid receipt path detected.');
        }

        if (basename($resolvedPath) !== $record->filename) {
            return $this->fail($adminId, $ip, $transactionId, 'Receipt filename mismatch with receipt record binding.');
        }

        if (! is_file($resolvedPath) || ! is_readable($resolvedPath)) {
            return $this->fail($adminId, $ip, $transactionId, 'Receipt file does not exist or is not readable.');
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            $dirPerms = fileperms($baseDirReal);
            $filePerms = fileperms($resolvedPath);

            if (($dirPerms !== false && ($dirPerms & 0x0002) === 0x0002) || ($filePerms !== false && ($filePerms & 0x0002) === 0x0002)) {
                return $this->fail($adminId, $ip, $transactionId, 'Receipt directory/file permissions are too permissive.');
            }

            @chmod($baseDirReal, 0750);
            @chmod($resolvedPath, 0640);
        }

        $mime = mime_content_type($resolvedPath) ?: '';
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

        if (! in_array($mime, $allowedMimes, true) || getimagesize($resolvedPath) === false) {
            return $this->fail($adminId, $ip, $transactionId, 'Invalid or unsupported image format.');
        }

        if ($record->mime_type !== $mime) {
            return $this->fail($adminId, $ip, $transactionId, 'MIME type does not match receipt record.');
        }

        if (! preg_match('/^[a-f0-9]{64}$/', (string) $record->sha256_hash)) {
            return $this->fail($adminId, $ip, $transactionId, 'Receipt record hash is invalid.');
        }

        $expectedChecksum = strtolower($record->sha256_hash);
        $actualChecksum = hash_file('sha256', $resolvedPath);

        if (! hash_equals($expectedChecksum, $actualChecksum)) {
            return $this->fail($adminId, $ip, $transactionId, 'Integrity check failed: receipt appears tampered.');
        }

        if (filled($record->size_bytes) && (int) $record->size_bytes !== (int) filesize($resolvedPath)) {
            return $this->fail($adminId, $ip, $transactionId, 'File size mismatch with receipt record.');
        }

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

        return response()->json([
            'ok' => true,
            'message' => 'Receipt validated and ready for secure processing.',
            'transaction_id' => $transactionId,
            'receipt_file' => $record->filename,
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
