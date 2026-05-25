<?php
// app/Listeners/NotifyAdminAnnoyingly.php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Services\AnnoyingNotificationService;
use App\Models\User;

class NotifyAdminAnnoyingly
{
    public function handle(OrderStatusUpdated $event)
    {
        $order = $event->order;
        
        // Only notify when status becomes 'ready_for_pickup'
        if ($order->status !== 'ready_for_pickup') {
            return;
        }
        
        $admins = User::where('user_type', 'admin')->get();
        
        foreach ($admins as $admin) {
            $title = "⚠️ ORDER READY - RIDER NEEDED! ⚠️";
            $message = "Order #{$order->order_number} is ready for pickup! Assign a rider NOW!";
            
            // Create sticky modal for admin
            AnnoyingNotificationService::createStickyModal(
                $admin->id,
                "🚨 RIDER ASSIGNMENT REQUIRED IMMEDIATELY 🚨",
                "⚠️ URGENT: Order #{$order->order_number} is ready for pickup!\n\n📍 Vendor: {$order->vendor->business_name}\n📍 Customer: {$order->delivery_address}\n💰 Order Total: KES " . number_format($order->total, 2) . "\n\n⚠️ No rider assigned yet! Please assign a rider immediately to avoid delivery delays!\n\nClick ACKNOWLEDGE to go to order assignment page.",
                'ready_for_pickup',
                $order->id
            );
            
            // Desktop notification
            AnnoyingNotificationService::sendDesktopNotification(
                $admin->id,
                $title,
                $message,
                'ready_for_pickup',
                $order->id
            );
            
            // Email notification
            AnnoyingNotificationService::sendEmailNotification(
                $admin,
                "⚠️ RIDER NEEDED FOR ORDER #{$order->order_number}",
                [
                    'type' => 'ready_for_pickup',
                    'order_number' => $order->order_number,
                    'vendor' => $order->vendor->business_name,
                    'address' => $order->delivery_address,
                    'total' => $order->total
                ],
                $order
            );
            
            // Push notification
            AnnoyingNotificationService::sendPushNotification(
                $admin->id,
                $title,
                $message,
                ['order_id' => $order->id, 'type' => 'admin', 'action' => 'assign_rider']
            );
        }
        
        \Log::info('Admins notified about ready_for_pickup', [
            'order_id' => $order->id,
            'admin_count' => $admins->count()
        ]);
    }
}