<?php namespace Redooor\Redminportal\App\Classes;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Page;

class DashboardStats
{
    public const REVENUE_STATUSES = ['Completed', 'Delivered'];

    private $now;

    private $timezone;

    public function __construct(?CarbonImmutable $now = null)
    {
        $this->timezone = config('app.timezone') ?: date_default_timezone_get();
        $this->now = ($now ?: CarbonImmutable::now())->setTimezone($this->timezone);
    }

    /**
     * Totals for orders created in the last 30 days.
     */
    public function orderTotals(): array
    {
        list($start, $end) = $this->window();

        $orders = DB::table('orders')
            ->whereBetween('created_at', [$this->format($start), $this->format($end)])
            ->get(['paid', 'payment_status']);

        $revenue = 0.0;

        foreach ($orders as $order) {
            if (in_array($order->payment_status, self::REVENUE_STATUSES, true)) {
                $revenue += (float) $order->paid;
            }
        }

        return [
            'orders' => $orders->count(),
            'revenue' => round($revenue, 2),
        ];
    }

    /**
     * Number of users created in the last 30 days.
     */
    public function newUsers(): int
    {
        list($start, $end) = $this->window();

        return DB::table('users')
            ->whereBetween('created_at', [$this->format($start), $this->format($end)])
            ->count();
    }

    /**
     * Number of products marked as active.
     */
    public function activeProducts(): int
    {
        return DB::table('products')->where('active', 1)->count();
    }

    /**
     * Zero-filled order and revenue buckets for each day in the window.
     */
    public function ordersPerDay(int $days = 30): array
    {
        list($start, $end) = $this->window($days);

        $labels = array();
        $orders = array();
        $revenue = array();

        for ($day = 0; $day < $days; $day++) {
            $labels[$day] = $start->addDays($day)->format('Y-m-d');
            $orders[$day] = 0;
            $revenue[$day] = 0.0;
        }

        $buckets = array_flip($labels);

        $rows = DB::table('orders')
            ->whereBetween('created_at', [$this->format($start), $this->format($end)])
            ->get(['created_at', 'paid', 'payment_status']);

        foreach ($rows as $row) {
            $date = CarbonImmutable::parse($row->created_at, $this->timezone)->format('Y-m-d');

            if (! isset($buckets[$date])) {
                continue;
            }

            $index = $buckets[$date];
            $orders[$index]++;

            if (in_array($row->payment_status, self::REVENUE_STATUSES, true)) {
                $revenue[$index] += (float) $row->paid;
            }
        }

        foreach ($revenue as $index => $value) {
            $revenue[$index] = round($value, 2);
        }

        return [
            'labels' => $labels,
            'orders' => $orders,
            'revenue' => $revenue,
        ];
    }

    /**
     * Number of orders in the window grouped by payment status.
     */
    public function ordersByStatus(): array
    {
        list($start, $end) = $this->window();

        $rows = DB::table('orders')
            ->whereBetween('created_at', [$this->format($start), $this->format($end)])
            ->get(['payment_status']);

        $statuses = array();

        foreach ($rows as $row) {
            $status = $row->payment_status === null ? 'Unknown' : (string) $row->payment_status;

            if (! isset($statuses[$status])) {
                $statuses[$status] = 0;
            }

            $statuses[$status]++;
        }

        return $statuses;
    }

    /**
     * Most ordered products for orders created in the window.
     */
    public function topProducts(int $limit = 5): array
    {
        list($start, $end) = $this->window();

        $rows = DB::table('order_product')
            ->join('orders', 'orders.id', '=', 'order_product.order_id')
            ->join('products', 'products.id', '=', 'order_product.product_id')
            ->whereBetween('orders.created_at', [$this->format($start), $this->format($end)])
            ->select('products.id', 'products.name', DB::raw('COUNT(order_product.id) as orders'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('orders')
            ->orderBy('products.name')
            ->limit($limit)
            ->get();

        $products = array();

        foreach ($rows as $row) {
            $products[] = array(
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'orders' => (int) $row->orders,
            );
        }

        return $products;
    }

    /**
     * Newest orders with their users.
     */
    public function recentOrders(int $limit = 5): Collection
    {
        return Order::with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Most recently updated pages.
     */
    public function recentPages(int $limit = 5): Collection
    {
        return Page::orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Inclusive start and end of the window.
     */
    private function window(int $days = 30): array
    {
        return array(
            $this->now->startOfDay()->subDays($days - 1),
            $this->now->endOfDay(),
        );
    }

    /**
     * Format a date for comparison against the database's local timestamps.
     */
    private function format(CarbonImmutable $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
