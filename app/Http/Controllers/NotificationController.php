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
        $stickyNotifications = session()->get('sticky_notifications', []);
        $annoyingNotifications = session()->get('annoying_notifications', []);
        
        // Combine both types
        $allNotifications = array_merge($stickyNotifications, $annoyingNotifications);
        
        return response()->json([
            'has_notifications' => count($allNotifications) > 0,
            'count' => count($allNotifications),
            'sticky_count' => count($stickyNotifications),
            'notifications' => $stickyNotifications // Return sticky ones for modal display
        ]);
    }
}