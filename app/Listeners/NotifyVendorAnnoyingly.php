<?php
// app/Listeners/NotifyVendorAnnoyingly.php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\AnnoyingNotificationService;

class NotifyVendorAnnoyingly
{
    public function handle(OrderPlaced $event)
    {
        $order = $event->order;
        $vendor = $order->vendor;
        $vendorUser = $vendor->user;
        
        \Log::info('NotifyVendorAnnoyingly triggered', [
            'order_id' => $order->id,
            'vendor_user_id' => $vendorUser->id,
            'vendor_email' => $vendorUser->email
        ]);
        
        $title = "🔴 NEW ORDER! ACT NOW! 🔴";
        $message = "Order #{$order->order_number} requires your immediate attention! Total: KES " . number_format($order->total, 2);
        
        // Create the sticky modal for vendor ONLY
        AnnoyingNotificationService::createStickyModal(
            $vendorUser->id,
            "🚨 URGENT: NEW ORDER #{$order->order_number}",
            "You have received a new order!\n\nOrder #: {$order->order_number}\nAmount: KES " . number_format($order->total, 2) . "\nCustomer: {$order->customer->name}\nItems: {$order->items->count()} item(s)\n\nPlease confirm immediately to avoid delays!\n\n⚠️ This is a priority alert!",
            'order_placed',
            $order->id
        );
        
        // Send desktop notification
        AnnoyingNotificationService::sendDesktopNotification(
            $vendorUser->id,
            $title,
            $message,
            'order_placed',
            $order->id
        );
        
        // Email notification
        AnnoyingNotificationService::sendEmailNotification(
            $vendorUser,
            "🔴 NEW ORDER RECEIVED - ACT NOW 🔴",
            [
                'type' => 'order_placed',
                'order_number' => $order->order_number,
                'total' => $order->total,
                'customer' => $order->customer->name,
                'items_count' => $order->items->count()
            ],
            $order
        );
        
        // PWA push notification
        AnnoyingNotificationService::sendPushNotification(
            $vendorUser->id,
            $title,
            $message,
            ['order_id' => $order->id, 'type' => 'vendor', 'action' => 'view_order']
        );
        
        \Log::info('Vendor notified annoyingly', [
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'vendor_email' => $vendorUser->email
        ]);
    }
}