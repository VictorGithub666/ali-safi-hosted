<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get order statistics
        $totalOrders = Order::where('customer_id', $user->id)->count();
        $pendingOrders = Order::where('customer_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed', 'preparing'])
            ->count();
        $completedOrders = Order::where('customer_id', $user->id)
            ->where('status', 'delivered')
            ->count();
        $totalSpent = Order::where('customer_id', $user->id)
            ->where('status', 'delivered')
            ->sum('total');
        
        // Get recent orders (last 5)
        $recentOrders = Order::where('customer_id', $user->id)
            ->with(['vendor.user', 'items.product'])
            ->latest()
            ->take(5)
            ->get();
        
        return view('customer.dashboard', compact(
            'totalOrders',
            'pendingOrders', 
            'completedOrders',
            'totalSpent',
            'recentOrders'
        ));
    }
}