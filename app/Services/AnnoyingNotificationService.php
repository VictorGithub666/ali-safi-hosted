<?php
// app/Services/AnnoyingNotificationService.php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AnnoyingNotificationService
{
    /**
     * Get sound URL - using your actual alarm_sound.mp3
     */
    public static function getAnnoyingSoundUrl($type)
    {
        return asset('sounds/alarm_sound.mp3');
    }

    /**
     * Send ANNOYING desktop notification (intrusive popup)
     * ONLY for the intended user type
     */
    public static function sendDesktopNotification($userId, $title, $body, $type, $orderId = null)
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $body,
            'type' => $type,
            'data' => ['order_id' => $orderId, 'requires_attention' => true],
            'is_read' => false,
        ]);

        // Store in session for immediate display - FIX: Only for the specific user's session
        // But since we can't target specific user's session easily, we'll use a different approach
        // Store in database and fetch via AJAX instead
        
        // Store as push notification for service worker
        self::sendPushNotification($userId, $title, $body, ['order_id' => $orderId, 'type' => $type]);

        return $notification;
    }

    /**
     * Send email notification with URGENT subject lines
     */
    public static function sendEmailNotification($user, $subject, $content, $order = null)
    {
        $urgentSubjects = [
            'order_placed' => '🔴 URGENT: NEW ORDER RECEIVED - IMMEDIATE ACTION REQUIRED 🔴',
            'ready_for_pickup' => '⚠️ ORDER READY FOR PICKUP - RIDER NEEDED IMMEDIATELY ⚠️',
            'rider_assigned' => '🚨 DELIVERY ASSIGNED - PICK UP NOW! 🚨',
            'order_picked_up' => '📦 ORDER PICKED UP - DELIVER ASAP! 📦'
        ];

        $emailSubject = $urgentSubjects[$content['type'] ?? ''] ?? '⚠️ URGENT: Action Required - Ali-Safi Platform ⚠️';
        
        $html = self::generateEmailHtml($user, $emailSubject, $content, $order);

        try {
            Mail::send([], [], function ($message) use ($user, $emailSubject, $html) {
                $message->to($user->email)
                        ->subject($emailSubject)
                        ->html($html, 'text/html');
            });
            Log::info('Email notification sent', ['user_id' => $user->id, 'subject' => $emailSubject]);
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Generate email HTML inline to avoid missing view
     */
    private static function generateEmailHtml($user, $subject, $content, $order)
    {
        $orderNumber = $content['order_number'] ?? ($order->order_number ?? 'N/A');
        $total = $content['total'] ?? ($order->total ?? 0);
        $customer = $content['customer'] ?? ($order->customer->name ?? 'N/A');
        $vendor = $content['vendor'] ?? ($order->vendor->business_name ?? 'N/A');
        $address = $content['address'] ?? ($order->delivery_address ?? 'N/A');
        
        $adminUrl = route('admin.orders.show', $order->id ?? 0);
        $vendorUrl = route('vendor.orders.show', $order->id ?? 0);
        $riderUrl = route('rider.deliveries.show', $order->id ?? 0);
        $customerUrl = route('customer.orders.track', $order->id ?? 0);
        
        $actionUrl = '#';
        if (isset($content['type'])) {
            switch ($content['type']) {
                case 'order_placed':
                    $actionUrl = $vendorUrl;
                    break;
                case 'ready_for_pickup':
                    $actionUrl = $adminUrl;
                    break;
                case 'rider_assigned':
                    $actionUrl = $riderUrl;
                    break;
                case 'order_picked_up':
                    $actionUrl = $customerUrl;
                    break;
            }
        }
        
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>⚠️ URGENT - Ali-Safi Alert ⚠️</title>
</head>
<body style="margin: 0; padding: 0; background: #ff0000;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border: 10px solid #ff0000;">
        <div style="background: #000; color: #ff0000; padding: 20px; text-align: center; font-size: 28px; font-weight: bold;">
            ⚠️⚠️⚠️ URGENT ACTION REQUIRED ⚠️⚠️⚠️
        </div>
        
        <div style="padding: 30px;">
            <h1 style="color: #ff0000; font-size: 36px; text-align: center;">🔴 {$subject} 🔴</h1>
            
            <div style="background: #fff3cd; border-left: 5px solid #ff0000; padding: 20px; margin: 20px 0;">
                <p style="font-size: 18px;"><strong>Order #:</strong> {$orderNumber}</p>
                <p style="font-size: 18px;"><strong>Amount:</strong> KES " . number_format($total, 2) . "</p>
                <p style="font-size: 18px;"><strong>Customer:</strong> {$customer}</p>
                <p style="font-size: 18px;"><strong>Vendor:</strong> {$vendor}</p>
                <p style="font-size: 18px;"><strong>Address:</strong> {$address}</p>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="{$actionUrl}" 
                   style="background: #ff0000; color: white; padding: 15px 30px; text-decoration: none; font-weight: bold; font-size: 20px; display: inline-block;">
                    🔥 CLICK HERE TO RESPOND NOW 🔥
                </a>
            </div>
            
            <p style="color: #666; font-size: 12px; text-align: center; margin-top: 30px;">
                This is an automated alert from Ali-Safi Platform. Please do not ignore this message.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
        return $html;
    }

    /**
     * Send PWA push notification (can wake phone)
     */
    public static function sendPushNotification($userId, $title, $body, $data = [])
    {
        // Store in database for the specific user
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $body,
            'type' => $data['type'] ?? 'system',
            'data' => $data,
            'is_read' => false,
        ]);
        
        // Store in cache for service worker polling
        $userPendingKey = 'push_pending_' . $userId;
        $existing = \Illuminate\Support\Facades\Cache::get($userPendingKey, []);
        $existing[] = [
            'id' => $notification->id,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
            'vibrate' => [500, 300, 500, 300, 1000, 500, 300, 500, 300, 2000],
            'require_interaction' => true,
            'silent' => false,
        ];
        \Illuminate\Support\Facades\Cache::put($userPendingKey, $existing, 300);
        
        // Also store in session for immediate display on page load
        // Only store for the user who is currently logged in
        if (auth()->check() && auth()->id() == $userId) {
            $stickyNotifications = session()->get('sticky_notifications', []);
            $stickyNotifications[] = [
                'id' => (string) $notification->id,
                'title' => $title,
                'message' => $body,
                'type' => $data['type'] ?? 'system',
                'order_id' => $data['order_id'] ?? null,
                'requires_confirmation' => true,
                'blocking' => true,
                'priority' => 'critical',
                'created_at' => now()->toDateTimeString()
            ];
            session()->put('sticky_notifications', $stickyNotifications);
        }
        
        Log::info('Push notification queued', ['user_id' => $userId, 'title' => $title]);
    }

    /**
     * Create STICKY modal notification that BLOCKS screen
     */
    public static function createStickyModal($userId, $title, $message, $type, $orderId = null)
    {
        // Only create for the currently logged in user
        if (!auth()->check() || auth()->id() != $userId) {
            // Store in database for later retrieval
            Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => ['order_id' => $orderId, 'requires_attention' => true],
                'is_read' => false,
            ]);
            return;
        }
        
        $stickyNotifications = session()->get('sticky_notifications', []);
        $stickyNotifications[] = [
            'id' => uniqid('sticky_'),
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'order_id' => $orderId,
            'requires_confirmation' => true,
            'blocking' => true,
            'priority' => 'critical',
            'created_at' => now()->toDateTimeString()
        ];
        
        // Limit to 5 notifications to prevent overflow
        if (count($stickyNotifications) > 5) {
            $stickyNotifications = array_slice($stickyNotifications, -5);
        }
        
        session()->put('sticky_notifications', $stickyNotifications);
        
        Log::info('Sticky modal created', [
            'user_id' => $userId,
            'type' => $type,
            'order_id' => $orderId,
            'notification_id' => end($stickyNotifications)['id']
        ]);
    }
    
    /**
     * Clear all sticky notifications for a user
     */
    public static function clearAllStickyModals()
    {
        session()->forget('sticky_notifications');
        session()->forget('annoying_notifications');
        Log::info('All sticky modals cleared', ['user_id' => auth()->id()]);
    }
    
    /**
     * Get pending notifications for a user via AJAX
     */
    public static function getPendingNotifications($userId)
    {
        $pending = \Illuminate\Support\Facades\Cache::get('push_pending_' . $userId, []);
        \Illuminate\Support\Facades\Cache::forget('push_pending_' . $userId);
        return $pending;
    }
}