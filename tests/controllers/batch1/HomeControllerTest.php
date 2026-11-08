<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Classes\DashboardStats;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\User;

class HomeControllerTest extends RedminBrowserTestCase
{
    /**
     * Initialize setup with seed and login as the seeded admin.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RedminSeeder::class);

        Auth::guard('redminguard')->loginUsingId(1);
    }

    /**
     * Create a restricted user in a group with the supplied permissions and log in.
     */
    private function loginWithPermissions(array $permissions)
    {
        $group = new Group;
        $group->name = 'Dashboard ' . uniqid();
        $group->permissions = json_encode($permissions);
        $group->save();

        $user = new User;
        $user->email = uniqid('dashboard') . '@example.com';
        $user->password = Hash::make('secret');
        $user->first_name = 'Dashboard';
        $user->last_name = 'User';
        $user->activated = 1;
        $user->save();
        $user->groups()->save($group);

        Auth::guard('redminguard')->loginUsingId($user->id);
    }

    public function testDashboardShowsCardsAndChartsForAdmin()
    {
        $this->call('GET', '/admin/dashboard');

        $this->assertResponseOk();
        $this->assertStringContainsString('id="chart-daily"', $this->response->getContent());
        $this->assertStringContainsString('id="dashboard-data"', $this->response->getContent());
        $this->assertStringContainsString(e("What's new in 2.0.0"), $this->response->getContent());
        $this->assertStringNotContainsString('0.58', $this->response->getContent());
        $this->assertStringContainsString('Revenue counts only Completed and Delivered orders.', $this->response->getContent());
        $this->assertStringContainsString('href="https://github.com/redooor/redminportal/blob/master/CHANGELOG.md"', $this->response->getContent());
    }

    public function testDashboardJsonIsValidAndEscapesHostileStatus()
    {
        $order = new Order;
        $order->user_id = 1;
        $order->paid = 1.00;
        $order->transaction_id = 'HOSTILE-STATUS';
        $order->payment_status = '</script><b>x</b>';
        $order->save();

        $this->call('GET', '/admin/dashboard');

        $this->assertResponseOk();

        $html = $this->response->getContent();
        $this->assertStringNotContainsString('</script><b>x</b>', $html);

        $this->assertSame(1, preg_match(
            '/<script type="application\/json" id="dashboard-data">(.*?)<\/script>/s',
            $html,
            $matches
        ));

        $data = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $data['status']['</script><b>x</b>']);
        $this->assertCount(30, $data['daily']['labels']);
    }

    public function testHostileProductNameIsEscapedInTopProducts()
    {
        $product = new Product;
        $product->name = '</script><b>x</b>';
        $product->sku = 'HOSTILE-PRODUCT';
        $product->short_description = 'Hostile product';
        $product->active = true;
        $product->save();

        $order = new Order;
        $order->user_id = 1;
        $order->paid = 1.00;
        $order->transaction_id = 'HOSTILE-PRODUCT-ORDER';
        $order->payment_status = 'Completed';
        $order->save();
        $order->products()->attach($product->id);

        $this->call('GET', '/admin/dashboard');

        $this->assertResponseOk();

        $html = $this->response->getContent();
        $this->assertStringContainsString('&lt;/script&gt;&lt;b&gt;x&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('</script><b>x</b>', $html);
    }

    public function testDashboardRunsNoQueriesForUnpermittedWidgets()
    {
        $spy = new DashboardStatsSpy;
        $this->app->instance(DashboardStats::class, $spy);

        $cases = array(
            'orders' => array(
                'permissions' => array('admin.view' => 1, 'admin.orders.view' => -1),
                'denied_methods' => array('orderTotals', 'ordersPerDay', 'ordersByStatus', 'recentOrders'),
            ),
            'products' => array(
                'permissions' => array('admin.view' => 1, 'admin.products.view' => -1),
                'denied_methods' => array('activeProducts', 'topProducts'),
            ),
            'users' => array(
                'permissions' => array('admin.view' => 1, 'admin.users.view' => -1),
                'denied_methods' => array('newUsers'),
            ),
            'pages' => array(
                'permissions' => array('admin.view' => 1, 'admin.pages.view' => -1),
                'denied_methods' => array('recentPages'),
            ),
        );

        foreach ($cases as $widget => $case) {
            $this->loginWithPermissions($case['permissions']);
            $spy->calls = array();

            $this->call('GET', '/admin/dashboard');

            $this->assertResponseOk();

            foreach ($case['denied_methods'] as $method) {
                $this->assertNotContains($method, $spy->calls, "$method should not run without $widget access");
            }

            $html = $this->response->getContent();

            if ($widget === 'orders') {
                $this->assertStringNotContainsString('id="chart-daily"', $html);
                $this->assertStringNotContainsString('id="dashboard-data"', $html);
                $this->assertStringNotContainsString('Top products', $html);
                $this->assertStringContainsString('New users', $html);
                $this->assertStringContainsString('Active products', $html);
                $this->assertStringContainsString('Recent pages', $html);
            }

            if ($widget === 'products') {
                $this->assertStringNotContainsString('Top products', $html);
                $this->assertStringNotContainsString('Active products', $html);
                $this->assertStringContainsString('id="chart-daily"', $html);
                $this->assertStringContainsString('New users', $html);
                $this->assertStringContainsString('Recent pages', $html);
            }

            if ($widget === 'users') {
                $this->assertStringNotContainsString('New users', $html);
                $this->assertStringContainsString('Active products', $html);
                $this->assertStringContainsString('id="chart-daily"', $html);
                $this->assertStringContainsString('Recent pages', $html);
            }

            if ($widget === 'pages') {
                $this->assertStringNotContainsString('Recent pages', $html);
                $this->assertStringContainsString('New users', $html);
                $this->assertStringContainsString('Active products', $html);
                $this->assertStringContainsString('id="chart-daily"', $html);
            }
        }

        // A user allowed only to view pages (plus the dashboard itself) must only run the pages query.
        $this->loginWithPermissions(array('admin.dashboard.view' => 1, 'admin.pages.view' => 1));
        $spy->calls = array();

        $this->call('GET', '/admin/dashboard');

        $this->assertResponseOk();
        $this->assertSame(array('recentPages'), $spy->calls);

        $html = $this->response->getContent();
        $this->assertStringContainsString('Recent pages', $html);
        $this->assertStringNotContainsString('New users', $html);
        $this->assertStringNotContainsString('Active products', $html);
        $this->assertStringNotContainsString('Top products', $html);
        $this->assertStringNotContainsString('id="chart-daily"', $html);
        $this->assertStringNotContainsString('id="dashboard-data"', $html);
    }

    /**
     * created_at and updated_at are nullable columns: a row without a timestamp must not break the dashboard.
     */
    public function testDashboardSurvivesNullTimestamps()
    {
        \Illuminate\Support\Facades\DB::table('orders')->insert(array(
            'user_id' => 1,
            'paid' => 5,
            'payment_status' => 'Completed',
            'created_at' => null,
            'updated_at' => null,
        ));
        \Illuminate\Support\Facades\DB::table('pages')->insert(array(
            'title' => 'Undated page',
            'slug' => 'undated-page',
            'content' => 'x',
            'private' => 0,
            'created_at' => null,
            'updated_at' => null,
        ));

        $this->call('GET', 'admin/dashboard');

        $this->assertResponseOk();
        $this->assertStringContainsString('Undated page', $this->response->getContent());
    }

    public function testDashboardOnEmptyDatabaseShowsEmptyStates()
    {
        $this->call('GET', '/admin/dashboard');

        $this->assertResponseOk();

        $html = $this->response->getContent();
        $this->assertStringContainsString('data-empty-state', $html);
        $this->assertStringContainsString('>0</', $html);
    }

    public function testDashboardFallsBackToEnglishForChineseLocales()
    {
        foreach (array('zh-cn', 'zh-tw') as $locale) {
            app()->setLocale($locale);

            $this->call('GET', '/admin/dashboard');

            $this->assertResponseOk();

            $html = $this->response->getContent();
            $this->assertStringContainsString(e("What's new in 2.0.0"), $html);
            $this->assertStringNotContainsString('redminportal::dashboard.', $html);
        }
    }
}

class DashboardStatsSpy extends DashboardStats
{
    public $calls = array();

    public function orderTotals(): array
    {
        $this->calls[] = 'orderTotals';

        return array('orders' => 0, 'revenue' => 0.0);
    }

    public function newUsers(): int
    {
        $this->calls[] = 'newUsers';

        return 0;
    }

    public function activeProducts(): int
    {
        $this->calls[] = 'activeProducts';

        return 0;
    }

    public function ordersPerDay(int $days = 30): array
    {
        $this->calls[] = 'ordersPerDay';

        $labels = array();
        for ($day = 0; $day < $days; $day++) {
            $labels[] = date('Y-m-d', strtotime('-' . ($days - 1 - $day) . ' days'));
        }

        return array(
            'labels' => $labels,
            'orders' => array_fill(0, $days, 0),
            'revenue' => array_fill(0, $days, 0.0),
        );
    }

    public function ordersByStatus(): array
    {
        $this->calls[] = 'ordersByStatus';

        return array();
    }

    public function topProducts(int $limit = 5): array
    {
        $this->calls[] = 'topProducts';

        return array();
    }

    public function recentOrders(int $limit = 5): Collection
    {
        $this->calls[] = 'recentOrders';

        return collect();
    }

    public function recentPages(int $limit = 5): Collection
    {
        $this->calls[] = 'recentPages';

        return collect();
    }
}
