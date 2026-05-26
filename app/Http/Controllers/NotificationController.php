<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Acknowledge an urgent notification
     */
    public function acknowledge(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|string',
            'order_id' => 'nullable|integer',
            'type' => 'nullable|string',
            'auto_acknowledged' => 'nullable|boolean'
        ]);
        
        // Remove from session storage (sticky_notifications)
        $stickyNotifications = session()->get('sticky_notifications', []);
        $filtered = array_filter($stickyNotifications, function($n) use ($request) {
            return $n['id'] !== $request->notification_id;
        });
        session()->put('sticky_notifications', array_values($filtered));
        
        // Also remove from annoying_notifications if present
        $annoyingNotifications = session()->get('annoying_notifications', []);
        $filteredAnnoying = array_filter($annoyingNotifications, function($n) use ($request) {
            return ($n['id'] ?? null) !== $request->notification_id;
        });
        session()->put('annoying_notifications', array_values($filteredAnnoying));
        
        // Update database notification if exists
        $notification = Notification::find($request->notification_id);
        if ($notification) {
            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
        
        // Log acknowledgement for audit
        Log::info('Urgent notification acknowledged', [
            'user_id' => auth()->id(),
            'user_type' => auth()->user()->user_type,
            'notification_id' => $request->notification_id,
            'order_id' => $request->order_id,
            'type' => $request->type,
            'auto_acknowledged' => $request->auto_acknowledged ?? false,
            'acknowledged_at' => now()
        ]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Check if there are pending notifications
     */
    public function check(Request $request)
    {
        $user = auth()->user();
        
        // Query DB for unread notifications for this user
        $dbNotifications = \App\Models\Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($n) {
                return [
                    'id'       => (string) $n->id,
                    'title'    => $n->title,
                    'message'  => $n->message,
                    'type'     => $n->data['original_type'] ?? $n->type,
                    'order_id' => $n->data['order_id'] ?? null,
                ];
            })->toArray();

        // Merge with any session-based ones (for backward compat)
        $stickyNotifications = session()->get('sticky_notifications', []);
        $allNotifications = array_merge($dbNotifications, $stickyNotifications);

        // Deduplicate by id
        $seen = [];
        $unique = [];
        foreach ($allNotifications as $n) {
            if (!in_array($n['id'], $seen)) {
                $seen[] = $n['id'];
                $unique[] = $n;
            }
        }

        return response()->json([
            'has_notifications' => count($unique) > 0,
            'count'             => count($unique),
            'notifications'     => $unique,
        ]);
    }
}