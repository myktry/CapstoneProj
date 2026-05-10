<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\AuthorizationException;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingNotificationController extends Controller
{
    public function markSeen(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            throw new AuthenticationException(
                'User not authenticated',
                'You must be logged in to perform this action.',
            );
        }

        try {
            $updated = $user->appointments()
                ->whereNull('seen_at')
                ->where('created_at', '>=', now()->subDay())
                ->update(['seen_at' => now()]);

            return response()->json([
                'ok' => true,
                'updated' => $updated,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to mark bookings as seen', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'error' => 'Failed to update bookings',
            ], 500);
        }
    }

    public function getNotifications(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            throw new AuthenticationException(
                'User not authenticated',
                'You must be logged in to perform this action.',
            );
        }

        try {
            $notifications = $user->notifications()
                ->latest()
                ->get();

            $unreadCount = $user->unreadNotifications()->count();

            return response()->json([
                'ok' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to retrieve notifications', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'error' => 'Failed to retrieve notifications',
            ], 500);
        }
    }

    public function markAsRead(Request $request, UserNotification $notification): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            throw new AuthenticationException(
                'User not authenticated',
                'You must be logged in to perform this action.',
            );
        }

        if ($notification->user_id !== $user->id) {
            throw new AuthorizationException(
                'User does not own this notification',
                'You do not have permission to access this notification.',
                context: ['notification_id' => $notification->id],
            );
        }

        try {
            $notification->markAsRead();

            return response()->json([
                'ok' => true,
                'notification' => $notification,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to mark notification as read', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'error' => 'Failed to update notification',
            ], 500);
        }
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            throw new AuthenticationException(
                'User not authenticated',
                'You must be logged in to perform this action.',
            );
        }

        try {
            $updated = $user->unreadNotifications()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            return response()->json([
                'ok' => true,
                'updated' => $updated,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'error' => 'Failed to update notifications',
            ], 500);
        }
    }
}
