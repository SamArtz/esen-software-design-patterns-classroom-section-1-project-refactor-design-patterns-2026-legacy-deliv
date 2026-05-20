<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $ordersByStatus = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        $activeVendors = Vendor::where('status', 'active')->count();
        $recentOrders = Order::with(['customer.user', 'vendor'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'totalOrders', 'ordersByStatus', 'activeVendors', 'recentOrders'
        ));
    }
}
