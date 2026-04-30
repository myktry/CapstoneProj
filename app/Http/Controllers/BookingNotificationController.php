<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingNotificationController extends Controller
{
    public function markSeen(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['ok' => false], 403);
        }

        $updated = $user->appointments()
            ->whereNull('seen_at')
            ->where('created_at', '>=', now()->subDay())
            ->update(['seen_at' => now()]);

        return response()->json([
            'ok' => true,
            'updated' => $updated,
        ]);
    }

    public function getNotifications(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['ok' => false], 403);
        }

        $notifications = $user->notifications()
            ->latest()
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'ok' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, UserNotification $notification): JsonResponse
    {
        $user = $request->user();

        if (! $user || $notification->user_id !== $user->id) {
            return response()->json(['ok' => false], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'ok' => true,
            'notification' => $notification,
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['ok' => false], 403);
        }

        $updated = $user->unreadNotifications()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'ok' => true,
            'updated' => $updated,
        ]);
    }
}
