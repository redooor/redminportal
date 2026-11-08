<?php namespace Redooor\Redminportal\Test;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Redooor\Redminportal\App\Classes\DashboardStats;
use Redooor\Redminportal\App\Models\Order;

class DashboardStatsTest extends RedminTestCase
{
    private $previousDefaultTimezone;
    private $previousAppTimezone;

    public function setUp(): void
    {
        parent::setUp();

        $this->previousDefaultTimezone = date_default_timezone_get();
        $this->previousAppTimezone = config('app.timezone');

        date_default_timezone_set('Asia/Singapore');
        config(['app.timezone' => 'Asia/Singapore']);
    }

    public function tearDown(): void
    {
        date_default_timezone_set($this->previousDefaultTimezone);
        config(['app.timezone' => $this->previousAppTimezone]);

        parent::tearDown();
    }

    /**
     * Test (Pass): only orders in the 30-day window are counted.
     */
    public function testOrderTotalsCountOnlyThirtyDayWindow()
    {
        $now = CarbonImmutable::now()->setTimezone('Asia/Singapore');
        $user_id = $this->createUser($now->subDays(60));

        $this->createOrder($user_id, 20.00, 'Completed', $now->subDays(5)->setTime(12, 0, 0));
        $this->createOrder($user_id, 99.00, 'Completed', $now->subDays(40)->setTime(12, 0, 0));
        $this->createOrder($user_id, 50.00, 'Completed', $now->addDay()->setTime(12, 0, 0));

        $stats = new DashboardStats($now);

        $this->assertSame(['orders' => 1, 'revenue' => 20.0], $stats->orderTotals());
    }

    /**
     * Test (Pass): revenue only includes Completed and Delivered orders.
     */
    public function testRevenueExcludesCanceledRefundedAndPending()
    {
        $now = CarbonImmutable::now()->setTimezone('Asia/Singapore');
        $user_id = $this->createUser($now->subDays(60));
        $created_at = $now->subDays(2)->setTime(10, 0, 0);

        $this->createOrder($user_id, 10.00, 'Completed', $created_at);
        $this->createOrder($user_id, 5.50, 'Delivered', $created_at);
        $this->createOrder($user_id, 7.00, 'Canceled', $created_at);
        $this->createOrder($user_id, 2.00, 'Refunded', $created_at);
        $this->createOrder($user_id, 3.00, 'Pending', $created_at);
        $this->createOrder($user_id, null, 'Completed', $created_at);

        $stats = new DashboardStats($now);

        $this->assertSame(['orders' => 6, 'revenue' => 15.5], $stats->orderTotals());
    }

    /**
     * Test (Pass): every one of the 30 days has a label and a zero-filled value.
     */
    public function testOrdersPerDayIsZeroFilledAndExactLength()
    {
        $now = CarbonImmutable::now()->setTimezone('Asia/Singapore');
        $user_id = $this->createUser($now->subDays(60));

        $this->createOrder($user_id, 12.34, 'Completed', $now->subDays(5)->setTime(12, 0, 0));

        $stats = new DashboardStats($now);
        $per_day = $stats->ordersPerDay();

        $this->assertCount(30, $per_day['labels']);
        $this->assertCount(30, $per_day['orders']);
        $this->assertCount(30, $per_day['revenue']);
        $this->assertSame($now->subDays(29)->format('Y-m-d'), $per_day['labels'][0]);
        $this->assertSame($now->format('Y-m-d'), $per_day['labels'][29]);
        $this->assertSame(1, $per_day['orders'][24]);
        $this->assertSame(12.34, $per_day['revenue'][24]);
        $this->assertSame(0, $per_day['orders'][0]);
        $this->assertSame(0.0, $per_day['revenue'][0]);
        $this->assertSame(1, array_sum($per_day['orders']));
    }

    /**
     * Test (Pass): buckets follow the app timezone and the inclusive window bounds.
     */
    public function testOrdersPerDayRespectsAppTimezoneAndBounds()
    {
        $now = CarbonImmutable::parse('2026-05-20 12:00:00', 'Asia/Singapore');
        $user_id = $this->createUser($now->subDays(60));

        // Adjacent local days: 23:59 and 00:01.
        $this->createOrder($user_id, 10.00, 'Completed', $now->subDay()->setTime(23, 59, 0));
        $this->createOrder($user_id, 10.00, 'Completed', $now->setTime(0, 1, 0));

        // Exactly on the window start, and one second before it.
        $start = $now->startOfDay()->subDays(29);
        $this->createOrder($user_id, 10.00, 'Completed', $start);
        $this->createOrder($user_id, 10.00, 'Completed', $start->subSecond());

        // Future-dated order, outside the window.
        $this->createOrder($user_id, 10.00, 'Completed', $now->addDay()->startOfDay());

        $stats = new DashboardStats($now);
        $per_day = $stats->ordersPerDay();

        $this->assertSame('2026-04-21', $per_day['labels'][0]);
        $this->assertSame('2026-05-19', $per_day['labels'][28]);
        $this->assertSame('2026-05-20', $per_day['labels'][29]);
        $this->assertSame(1, $per_day['orders'][0]);
        $this->assertSame(10.0, $per_day['revenue'][0]);
        $this->assertSame(1, $per_day['orders'][28]);
        $this->assertSame(1, $per_day['orders'][29]);
        $this->assertSame(3, array_sum($per_day['orders']));
    }

    /**
     * Test (Pass): null payment status is reported as Unknown.
     */
    public function testOrdersByStatusUsesUnknownForNull()
    {
        $now = CarbonImmutable::now()->setTimezone('Asia/Singapore');
        $user_id = $this->createUser($now->subDays(60));
        $created_at = $now->subDays(2)->setTime(10, 0, 0);

        $this->createOrder($user_id, 10.00, 'Completed', $created_at);
        $this->createOrder($user_id, 10.00, 'Completed', $created_at);
        $this->createOrder($user_id, 10.00, 'Pending', $created_at);
        $this->createOrder($user_id, 10.00, null, $created_at);
        $this->createOrder($user_id, 10.00, 'Delivered', $created_at);
        $this->createOrder($user_id, 10.00, 'Canceled', $now->subDays(40)->setTime(10, 0, 0));

        $statuses = (new DashboardStats($now))->ordersByStatus();

        $this->assertSame(2, $statuses['Completed']);
        $this->assertSame(1, $statuses['Pending']);
        $this->assertSame(1, $statuses['Unknown']);
        $this->assertSame(1, $statuses['Delivered']);
        $this->assertArrayNotHasKey('Canceled', $statuses);
    }

    /**
     * Test (Pass): top products are limited, ordered by count and filtered by window.
     */
    public function testTopProductsOrderAndLimit()
    {
        $now = CarbonImmutable::now()->setTimezone('Asia/Singapore');
        $user_id = $this->createUser($now->subDays(60));
        $created_at = $now->subDays(2)->setTime(12, 0, 0);

        $products = [];
        foreach (range('A', 'F') as $index => $letter) {
            $products[$letter] = $this->createProduct('Product ' . $letter, true, $created_at);
        }

        // Product A is ordered once, B twice, ... and F six times.
        foreach ($products as $letter => $product_id) {
            $order_count = ord($letter) - ord('A') + 1;

            for ($i = 0; $i < $order_count; $i++) {
                $order_id = $this->createOrder($user_id, 10.00, 'Completed', $created_at);
                $this->attachProductToOrder($order_id, $product_id);
            }
        }

        // An old product with many old order lines must not appear.
        $old_product_id = $this->createProduct('Old Product', true, $now->subDays(40));
        for ($i = 0; $i < 10; $i++) {
            $old_order_id = $this->createOrder(
                $user_id,
                10.00,
                'Completed',
                $now->subDays(40)->setTime(12, 0, 0)
            );
            $this->attachProductToOrder($old_order_id, $old_product_id);
        }

        $top = (new DashboardStats($now))->topProducts();

        $this->assertCount(5, $top);
        $this->assertSame([
            $products['F'],
            $products['E'],
            $products['D'],
            $products['C'],
            $products['B'],
        ], array_column($top, 'id'));
        $this->assertSame([6, 5, 4, 3, 2], array_column($top, 'orders'));
        $this->assertSame('Product F', $top[0]['name']);
        $this->assertNotContains($old_product_id, array_column($top, 'id'));
    }

    /**
     * Test (Pass): an order whose user was deleted still loads with a null user.
     */
    public function testRecentOrdersSurviveDeletedUser()
    {
        $now = CarbonImmutable::now()->setTimezone('Asia/Singapore');
        $user_id = $this->createUser($now->subDays(2));
        $order_id = $this->createOrder($user_id, 10.00, 'Completed', $now->subDays(1));

        Schema::withoutForeignKeyConstraints(function () use ($user_id) {
            DB::table('users')->where('id', $user_id)->delete();
        });

        $orders = (new DashboardStats($now))->recentOrders();

        $this->assertCount(1, $orders);
        $this->assertInstanceOf(Order::class, $orders->first());
        $this->assertSame($order_id, $orders->first()->id);
        $this->assertTrue($orders->first()->relationLoaded('user'));
        $this->assertNull($orders->first()->user);
    }

    /**
     * Test (Pass): pages are returned with the most recently updated first.
     */
    public function testRecentPagesNewestUpdatedFirst()
    {
        $now = CarbonImmutable::now()->setTimezone('Asia/Singapore');

        $this->createPage('Oldest Page', $now->subDays(2));
        $this->createPage('Middle Page', $now->subDays(1));
        $this->createPage('Newest Page', $now);

        $stats = new DashboardStats($now);

        $this->assertSame(
            ['Newest Page', 'Middle Page', 'Oldest Page'],
            $stats->recentPages()->pluck('title')->all()
        );
        $this->assertSame(['Newest Page', 'Middle Page'], $stats->recentPages(2)->pluck('title')->all());
    }

    /**
     * Test (Pass): a database with no data returns zeroes and empty lists.
     */
    public function testEmptyDatabaseReturnsZeroesAndEmptyLists()
    {
        $now = CarbonImmutable::now()->setTimezone('Asia/Singapore');
        $stats = new DashboardStats($now);

        $this->assertSame(['orders' => 0, 'revenue' => 0.0], $stats->orderTotals());
        $this->assertSame(0, $stats->newUsers());
        $this->assertSame(0, $stats->activeProducts());
        $this->assertSame([], $stats->ordersByStatus());
        $this->assertSame([], $stats->topProducts());
        $this->assertCount(0, $stats->recentOrders());
        $this->assertCount(0, $stats->recentPages());

        $per_day = $stats->ordersPerDay();

        $this->assertCount(30, $per_day['labels']);
        $this->assertSame(array_fill(0, 30, 0), $per_day['orders']);
        $this->assertSame(array_fill(0, 30, 0.0), $per_day['revenue']);
    }

    private function createUser(CarbonImmutable $created_at)
    {
        return DB::table('users')->insertGetId([
            'email' => 'user' . uniqid() . '@example.com',
            'password' => 'secret',
            'first_name' => 'Test',
            'last_name' => 'User',
            'created_at' => $created_at->format('Y-m-d H:i:s'),
            'updated_at' => $created_at->format('Y-m-d H:i:s'),
        ]);
    }

    private function createOrder($user_id, $paid, $payment_status, CarbonImmutable $created_at)
    {
        $order = [
            'user_id' => $user_id,
            'payment_status' => $payment_status,
            'created_at' => $created_at->format('Y-m-d H:i:s'),
            'updated_at' => $created_at->format('Y-m-d H:i:s'),
        ];

        if ($paid !== null) {
            $order['paid'] = $paid;
        }

        return DB::table('orders')->insertGetId($order);
    }

    private function createProduct($name, $active, CarbonImmutable $created_at)
    {
        return DB::table('products')->insertGetId([
            'name' => $name,
            'sku' => 'SKU' . uniqid(),
            'short_description' => 'Short description',
            'active' => $active ? 1 : 0,
            'created_at' => $created_at->format('Y-m-d H:i:s'),
            'updated_at' => $created_at->format('Y-m-d H:i:s'),
        ]);
    }

    private function createPage($title, CarbonImmutable $updated_at)
    {
        return DB::table('pages')->insertGetId([
            'title' => $title,
            'slug' => 'slug' . uniqid(),
            'content' => 'Page content',
            'private' => 1,
            'created_at' => $updated_at->format('Y-m-d H:i:s'),
            'updated_at' => $updated_at->format('Y-m-d H:i:s'),
        ]);
    }

    private function attachProductToOrder($order_id, $product_id)
    {
        DB::table('order_product')->insert([
            'order_id' => $order_id,
            'product_id' => $product_id,
        ]);
    }
}
