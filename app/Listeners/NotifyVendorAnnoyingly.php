<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\AnnoyingNotificationService;
use App\Models\User;

class NotifyVendorAnnoyingly
{
    public function handle(OrderPlaced $event)
    {
        $order = $event->order;
        $vendor = $order->vendor;
        $vendorUser = $vendor->user;
        
        $title = "🔴 NEW ORDER! ACT NOW! 🔴";
        $message = "Order #{$order->order_number} requires your immediate attention! Total: KES " . number_format($order->total, 2);
        
        // Create the sticky modal directly (most intrusive)
        AnnoyingNotificationService::createStickyModal(
            $vendorUser->id,
            "🚨 URGENT: NEW ORDER #{$order->order_number}",
            "You have received a new order!\n\nOrder #: {$order->order_number}\nAmount: KES " . number_format($order->total, 2) . "\nCustomer: {$order->customer->name}\nItems: {$order->items->count()} item(s)\n\nPlease confirm immediately to avoid delays!",
            'order_placed',
            $order->id
        );
        
        // Also send desktop notification
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
            ['order_id' => $order->id, 'type' => 'vendor']
        );
        
        \Log::info('Vendor notified annoyingly', [
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'vendor_email' => $vendorUser->email
        ]);
    }
}