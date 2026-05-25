<?php
// app/Listeners/NotifyRiderAnnoyingly.php

namespace App\Listeners;

use App\Events\RiderAssigned;
use App\Services\AnnoyingNotificationService;
use App\Models\Rider;

class NotifyRiderAnnoyingly
{
    public function handle(RiderAssigned $event)
    {
        $order = $event->order;
        $rider = $order->rider;
        
        if (!$rider) return;
        
        $riderUser = $rider->user;
        
        $title = "🚨 YOU'VE BEEN ASSIGNED A DELIVERY! 🚨";
        $message = "Order #{$order->order_number} assigned to you! Pick up from {$order->vendor->business_name} NOW!";
        
        // Create sticky modal for rider
        AnnoyingNotificationService::createStickyModal(
            $riderUser->id,
            "🔥 NEW DELIVERY ASSIGNMENT - PICK UP NOW! 🔥",
            "🚚 DELIVERY ASSIGNMENT 🚚\n\nOrder #: {$order->order_number}\n📍 Pickup from: {$order->vendor->business_name}\n📍 Deliver to: {$order->delivery_address}\n💰 Delivery Fee: KES " . number_format($order->delivery_fee, 2) . "\n\n⚠️ Please pick up the order immediately!\n\nClick ACKNOWLEDGE to view delivery details and navigate.",
            'rider_assigned',
            $order->id
        );
        
        // Desktop notification
        AnnoyingNotificationService::sendDesktopNotification(
            $riderUser->id,
            $title,
            $message,
            'rider_assigned',
            $order->id
        );
        
        // Email notification
        AnnoyingNotificationService::sendEmailNotification(
            $riderUser,
            "🚨 NEW DELIVERY ASSIGNMENT - PICK UP NOW 🚨",
            [
                'type' => 'rider_assigned',
                'order_number' => $order->order_number,
                'vendor' => $order->vendor->business_name,
                'customer_address' => $order->delivery_address,
                'delivery_fee' => $order->delivery_fee
            ],
            $order
        );
        
        // Push notification with stronger vibration
        AnnoyingNotificationService::sendPushNotification(
            $riderUser->id,
            $title,
            $message,
            ['order_id' => $order->id, 'type' => 'rider', 'action' => 'view_delivery']
        );
        
        \Log::info('Rider notified about assignment', [
            'order_id' => $order->id,
            'rider_id' => $rider->id,
            'rider_email' => $riderUser->email
        ]);
    }
}