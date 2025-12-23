<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
// use App\Models\Order;

class DashboardController extends Controller
{
    public function index(){
        $stats = [
            'total_users' => User::count(),
            'total_orders' => 0,
            'total_revenue' => 0,
            'pending_orders' => 0,
            'low_stock' => 0,
            'revenue' => 0,
        ];
        
        $recentOrders = [];

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
