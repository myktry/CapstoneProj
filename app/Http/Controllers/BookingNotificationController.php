<?php

namespace App\Http\Controllers;

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
}
