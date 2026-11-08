<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Redooor\Redminportal\App\Classes\DashboardStats;

class HomeController extends Controller
{
    /**
     * Show the dashboard.
     *
     * Each widget is only queried when the signed in user may view its route.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home()
    {
        $user = Auth::guard('redminguard')->user();

        $can = array();

        foreach (array('orders', 'products', 'users', 'pages') as $widget) {
            $can[$widget] = $user->hasAccess(Request::create('admin/' . $widget, 'GET'));
        }

        $stats = app(DashboardStats::class);

        $data = array(
            'order_totals' => $can['orders'] ? $stats->orderTotals() : null,
            'new_users' => $can['users'] ? $stats->newUsers() : null,
            'active_products' => $can['products'] ? $stats->activeProducts() : null,
            'daily' => $can['orders'] ? $stats->ordersPerDay() : null,
            'status' => $can['orders'] ? $stats->ordersByStatus() : null,
            'top_products' => $can['orders'] && $can['products'] ? $stats->topProducts() : null,
            'recent_orders' => $can['orders'] ? $stats->recentOrders() : null,
            'recent_pages' => $can['pages'] ? $stats->recentPages() : null,
        );

        $payload = null;

        if ($can['orders']) {
            $payload = array(
                'daily' => $data['daily'],
                'status' => $data['status'],
                'names' => array(
                    'orders' => trans('redminportal::dashboard.orders'),
                    'revenue' => trans('redminportal::dashboard.revenue'),
                ),
            );
        }

        return view('redminportal::pages.home', array(
            'stats' => $data,
            'can' => $can,
            'payload' => $payload,
        ));
    }
}
