<?php
namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use App\Events\OrderPlaced;
use App\Events\OrderStatusUpdated;
use App\Events\RiderAssigned;          // ← FIX: was missing!
use App\Listeners\SendVendorWhatsAppNotification;
use App\Listeners\SendAdminWhatsAppNotification;
use App\Listeners\DeductProductStockOnDelivery;
use App\Listeners\NotifyVendorAnnoyingly;
use App\Listeners\NotifyAdminAnnoyingly;
use App\Listeners\NotifyRiderAnnoyingly;
use App\Listeners\NotifyCustomerAnnoyingly;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderStatusUpdated::class => [
            DeductProductStockOnDelivery::class,
        ],
        OrderPlaced::class => [
            SendVendorWhatsAppNotification::class,
            SendAdminWhatsAppNotification::class,
        ],
        RiderAssigned::class => [
        ],
    ];

    public function boot(): void
    {
        Order::observe(OrderObserver::class);
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}