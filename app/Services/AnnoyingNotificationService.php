<?php

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
        // Use your existing alarm_sound.mp3 for all types
        return '/sounds/alarm_sound.mp3';
    }

    /**
     * Send ANNOYING desktop notification (intrusive popup)
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

        // Store in session for immediate display on next page load (using annoying_notifications)
        $annoyingNotifications = session()->get('annoying_notifications', []);
        $annoyingNotifications[] = [
            'id' => (string) $notification->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'order_id' => $orderId,
            'sound_url' => self::getAnnoyingSoundUrl($type),
            'requires_action' => true,
            'intrusive' => true
        ];
        session()->put('annoying_notifications', $annoyingNotifications);
        
        // ALSO create sticky modal for guaranteed visibility
        self::createStickyModal($userId, $title, $body, $type, $orderId);

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

        $emailSubject = $urgentSubjects[$content['type']] ?? '⚠️ URGENT: Action Required - Ali-Safi Platform ⚠️';
        
        // Simple HTML email - no external view needed
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
        $orderNumber = $content['order_number'] ?? $order->order_number ?? 'N/A';
        $total = $content['total'] ?? ($order->total ?? 0);
        $customer = $content['customer'] ?? ($order->customer->name ?? 'N/A');
        $vendor = $content['vendor'] ?? ($order->vendor->business_name ?? 'N/A');
        $address = $content['address'] ?? $order->delivery_address ?? 'N/A';
        
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
                <a href="' . url('/') . '" 
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
        // Store for push service worker
        $pushQueue = session()->get('push_notifications', []);
        $pushQueue[] = [
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'timestamp' => now(),
            'vibrate' => [500, 300, 500, 300, 1000, 500, 300, 500, 300, 2000],
            'require_interaction' => true,
            'silent' => false,
            'actions' => [
                ['action' => 'view', 'title' => '🔥 VIEW NOW 🔥'],
                ['action' => 'snooze', 'title' => '⏰ Remind in 1 min'],
                ['action' => 'dismiss', 'title' => '❌ Dismiss']
            ]
        ];
        session()->put('push_notifications', $pushQueue);
        
        // Also store in cache for service worker to pick up
        \Illuminate\Support\Facades\Cache::put('push_pending_' . $userId, true, 300);
        
        Log::info('Push notification queued', ['user_id' => $userId, 'title' => $title]);
    }

    /**
     * Create STICKY modal notification that BLOCKS screen
     */
    public static function createStickyModal($userId, $title, $message, $type, $orderId = null)
    {
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
}