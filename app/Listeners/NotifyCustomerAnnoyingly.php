<?php
// app/Listeners/NotifyCustomerAnnoyingly.php - COMMENT OUT OR DELETE

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Services\AnnoyingNotificationService;
use Illuminate\Support\Facades\Log;

class NotifyCustomerAnnoyingly
{
    public function handle(OrderStatusUpdated $event)
    {
        $order = $event->order;
        
        // Only notify when status becomes 'picked_up'
        if ($order->status !== 'picked_up') {
            return;
        }
        
        $customer = $order->customer;
        
        Log::info('NotifyCustomerAnnoyingly triggered', [
            'order_id' => $order->id,
            'customer_id' => $customer->id
        ]);
        
        $title = "🎉 YOUR ORDER HAS BEEN PICKED UP! 🎉";
        $message = "Order #{$order->order_number} is on its way! Track your delivery NOW!";
        
        // Create sticky modal for customer
        AnnoyingNotificationService::createStickyModal(
            $customer->id,
            "🚚 YOUR ORDER IS ON THE WAY! 🚚",
            "🎉 Great news! 🎉\n\nA rider has picked up your order #{$order->order_number} and is heading to your location.\n\n📍 Delivery to: {$order->delivery_address}\n📦 Items: {$order->items->count()} item(s)\n\n🔔 Click ACKNOWLEDGE to track your delivery in real-time!",
            'order_picked_up',
            $order->id
        );
        
        // Desktop notification
        AnnoyingNotificationService::sendDesktopNotification(
            $customer->id,
            $title,
            $message,
            'order_picked_up',
            $order->id
        );
        
        // Email notification
        $riderName = $order->rider && $order->rider->user ? $order->rider->user->name : 'Rider';
        
        AnnoyingNotificationService::sendEmailNotification(
            $customer,
            "🎉 YOUR ORDER IS ON THE WAY! TRACK NOW 🎉",
            [
                'type' => 'order_picked_up',
                'order_number' => $order->order_number,
                'rider_name' => $riderName,
                'estimated_arrival' => now()->addMinutes(30)->format('H:i'),
                'total' => $order->total
            ],
            $order
        );
        
        // Push notification
        AnnoyingNotificationService::sendPushNotification(
            $customer->id,
            $title,
            $message,
            ['order_id' => $order->id, 'type' => 'customer', 'action' => 'track_order']
        );
        
        Log::info('Customer notified about pickup', [
            'order_id' => $order->id,
            'customer_id' => $customer->id
        ]);
    }
}