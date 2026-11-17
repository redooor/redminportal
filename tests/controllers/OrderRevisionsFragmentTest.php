<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Redooor\Redminportal\App\Http\Controllers\OrderController;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\User;

/**
 * Covers the order revision history fragment and full page renderings.
 */
class OrderRevisionsFragmentTest extends RedminBrowserTestCase
{
    private $order;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RedminSeeder::class);
        Auth::guard('redminguard')->loginUsingId(1);

        Order::flushEventListeners();
        Order::boot();

        $this->order = $this->createOrder();
    }

    /**
     * Test (Pass): Ajax returns only the revision table fragment.
     */
    public function testAjaxReturnsTableFragment()
    {
        $this->order->payment_status = 'Completed';
        $this->order->save();

        $revision = $this->order->revisions()->orderByDesc('id')->first();
        $response = $this->getRevisions($this->order);

        $response->assertOk();
        $response->assertHeader('X-Redmin-Fragment', '1');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertSee('revision-user', false);
        $response->assertSee('revision-date', false);
        $response->assertSee('revision-field', false);
        $response->assertSee('revision-old-value', false);
        $response->assertSee('revision-new-value', false);
        $response->assertSee('System Admin');
        $response->assertSee($revision->created_at->toDateTimeString());
        $response->assertSee('Payment Status');
        $response->assertSee('Pending');
        $response->assertSee('Completed');
        $response->assertDontSee('<html', false);
    }

    /**
     * Test (Pass): an order without revisions gets the empty message box.
     */
    public function testOrderWithoutRevisionsShowsEmptyMessage()
    {
        $this->order->revisions()->delete();

        $response = $this->getRevisions($this->order);

        $response->assertOk();
        $response->assertSee(trans('redminportal::messages.no_revision_found'));
        $response->assertSee('bg-body-secondary', false);
        $response->assertDontSee('revision-user', false);
    }

    /**
     * Test (Pass): a non-Ajax visit renders the full admin page with the table.
     */
    public function testNonAjaxReturnsFullPage()
    {
        $this->order->payment_status = 'Completed';
        $this->order->save();

        $response = $this->getRevisions($this->order, false);

        $response->assertOk();
        $response->assertHeaderMissing('X-Redmin-Fragment');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertSee('<html', false);
        $response->assertSee('revision-user', false);
        $response->assertSee('Payment Status');
    }

    /**
     * Test (Fail): a missing order answers 404 for Ajax and for pages.
     */
    public function testMissingOrderReturns404()
    {
        $response = $this->getRevisionsById(999999);

        $response->assertStatus(404);
        $response->assertHeaderMissing('X-Redmin-Fragment');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertSee(trans('redminportal::messages.error_find_no_such_record'));
        $response->assertDontSee('revision-user', false);

        $response = $this->getRevisionsById(999999, false);

        $response->assertStatus(404);
        $response->assertHeaderMissing('X-Redmin-Fragment');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertSee('Oops, 404!');
    }

    /**
     * Test (Pass): revision values and the editor name are HTML escaped.
     */
    public function testRevisionValuesAndUserNameAreEscaped()
    {
        $this->order->transaction_id = '<script>alert(1)</script>';
        $this->order->save();
        $this->order->transaction_id = '"><img src=x onerror=alert(1)>';
        $this->order->save();

        $user = Auth::guard('redminguard')->user();
        $user->first_name = '<b>';
        $user->save();

        $response = $this->getRevisions($this->order);

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
        $response->assertDontSee('"><img src=x onerror=alert(1)>', false);
        $response->assertSee('&quot;&gt;&lt;img src=x onerror=alert(1)&gt;', false);
        $response->assertDontSee('<b>', false);
        $response->assertSee('&lt;b&gt;', false);
    }

    /**
     * Test (Fail): a non-Ajax guest is redirected to the login page.
     */
    public function testGuestNonAjaxIsRedirectedToLogin()
    {
        $this->order->payment_status = 'Completed';
        $this->order->save();

        Auth::guard('redminguard')->logout();
        Auth::forgetGuards();

        $response = $this->getRevisions($this->order, false);

        $response->assertStatus(302);
        $response->assertRedirect('login');
        $response->assertHeaderMissing('X-Redmin-Fragment');
        $response->assertDontSee('revision-user', false);
        $response->assertDontSee('Completed');
    }

    /**
     * Test (Fail): an Ajax guest gets no fragment header and no revision data.
     */
    public function testGuestAjaxNeverReceivesFragment()
    {
        $this->order->payment_status = 'Completed';
        $this->order->save();

        Auth::guard('redminguard')->logout();
        Auth::forgetGuards();

        $response = $this->getRevisions($this->order);

        $response->assertOk();
        $response->assertHeaderMissing('X-Redmin-Fragment');
        $response->assertDontSee('revision-user', false);
        $response->assertDontSee('Completed');
        $response->assertDontSee('revision-limit-view', false);
    }

    /**
     * Test (Fail): a deactivated user never receives revision data.
     */
    public function testDeactivatedUserNeverReceivesRevisionData()
    {
        $this->order->payment_status = 'Completed';
        $this->order->save();

        $user = Auth::guard('redminguard')->user();
        $user->activated = 0;
        $user->save();
        Auth::forgetGuards();

        $response = $this->getRevisions($this->order);

        $response->assertStatus(302);
        $response->assertRedirect('login');
        $response->assertHeaderMissing('X-Redmin-Fragment');
        $response->assertDontSee('revision-user', false);
        $response->assertDontSee('Completed');
    }

    /**
     * Test (Fail): a user without the revisions permission is redirected.
     */
    public function testUserWithoutRevisionPermissionIsDenied()
    {
        $this->order->payment_status = 'Completed';
        $this->order->save();

        $user = $this->createUserWithPermissions(array(
            'admin.orders.view' => 1,
            'admin.orders.revisions.view' => -1
        ));
        $this->loginAs($user);

        $response = $this->getRevisions($this->order);

        $response->assertStatus(302);
        $response->assertRedirect('login/unauthorized');
        $response->assertHeaderMissing('X-Redmin-Fragment');
        $response->assertDontSee('revision-user', false);
        $response->assertDontSee('Completed');

        $user = $this->createUserWithPermissions(array('admin.dashboard.view' => 1));
        $this->loginAs($user);

        $response = $this->getRevisions($this->order);

        $response->assertStatus(302);
        $response->assertRedirect('login/unauthorized');
        $response->assertHeaderMissing('X-Redmin-Fragment');
        $response->assertDontSee('revision-user', false);
    }

    /**
     * Test (Pass): a user with the revisions permission receives the fragment.
     */
    public function testUserWithRevisionPermissionReceivesFragment()
    {
        $this->order->payment_status = 'Completed';
        $this->order->save();

        $user = $this->createUserWithPermissions(array(
            'admin.orders.revisions.view' => 1
        ));
        $this->loginAs($user);

        $response = $this->getRevisions($this->order);

        $response->assertOk();
        $response->assertHeader('X-Redmin-Fragment', '1');
        $response->assertSee('revision-user', false);
        $response->assertSee('Payment Status');
    }

    /**
     * Test (Fail): a changed password fingerprint ends fragment access.
     */
    public function testChangedPasswordFingerprintEndsFragmentAccess()
    {
        $this->order->payment_status = 'Completed';
        $this->order->save();

        $this->call('GET', '/admin/orders/revisions/' . $this->order->id);

        $user = User::find(1);
        $this->assertSessionHas('redmin_password_fingerprint', hash('sha256', $user->password));

        Auth::forgetGuards();
        $user->password = Hash::make('changed-password');
        $user->save();

        $response = $this->getRevisions($this->order);

        $response->assertOk();
        $response->assertHeaderMissing('X-Redmin-Fragment');
        $response->assertDontSee('revision-user', false);
        $response->assertDontSee('Completed');
    }

    /**
     * Test (Pass): a published view without the table partial still renders.
     */
    public function testPublishedViewWithoutTableFallsBackToItsView()
    {
        $this->order->payment_status = 'Completed';
        $this->order->save();

        $directory = sys_get_temp_dir() . '/redminportal-published-' . uniqid();
        $viewDirectory = $directory . '/revisions';
        mkdir($viewDirectory, 0777, true);
        $viewFile = $viewDirectory . '/view.blade.php';
        file_put_contents($viewFile, 'Published revision view for order {{ $model->id }}');

        $finder = view()->getFinder();
        $originalHints = $finder->getHints();

        try {
            $finder->replaceNamespace('redminportal', array($directory));
            $finder->flush();

            $response = $this->getRevisions($this->order);

            $response->assertOk();
            $response->assertHeader('X-Redmin-Fragment', '1');
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $response->assertSee('Published revision view for order ' . $this->order->id);
            $response->assertDontSee('revision-user', false);
        } finally {
            $finder->replaceNamespace('redminportal', $originalHints['redminportal']);
            $finder->flush();

            @unlink($viewFile);
            @rmdir($viewDirectory);
            @rmdir($directory);
        }
    }

    /**
     * Test (Pass): the route and the shared trait method signature exist.
     */
    public function testRouteAndTraitSignature()
    {
        $found = null;

        foreach (Route::getRoutes() as $route) {
            if ($route->uri() === 'admin/orders/revisions/{sid}') {
                $found = $route;
                break;
            }
        }

        $this->assertNotNull($found);
        $this->assertContains('GET', $found->methods());

        $method = new ReflectionMethod(OrderController::class, 'getRevisions');

        $this->assertSame(1, $method->getNumberOfParameters());
        $this->assertSame(1, $method->getNumberOfRequiredParameters());
        $this->assertSame('sid', $method->getParameters()[0]->getName());
    }

    /**
     * Create an order with the signed in user as its owner.
     */
    private function createOrder(array $overrides = array())
    {
        $order = new Order;
        $order->user_id = Auth::guard('redminguard')->id();
        $order->paid = 10.99;
        $order->transaction_id = isset($overrides['transaction_id'])
            ? $overrides['transaction_id']
            : 'TXN-' . uniqid();
        $order->payment_status = isset($overrides['payment_status'])
            ? $overrides['payment_status']
            : 'Pending';
        $order->save();

        return $order;
    }

    /**
     * Call the revisions route for an order.
     */
    private function getRevisions($order, $ajax = true)
    {
        return $this->getRevisionsById($order->id, $ajax);
    }

    /**
     * Call the revisions route by order id.
     */
    private function getRevisionsById($sid, $ajax = true)
    {
        $server = $ajax ? array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest') : array();

        return $this->call('GET', '/admin/orders/revisions/' . $sid, array(), array(), array(), $server);
    }

    /**
     * Create an activated user in a group with the given permissions.
     */
    private function createUserWithPermissions(array $permissions)
    {
        $user = new User;
        $user->email = 'fragment-' . uniqid() . '@test.com';
        $user->password = Hash::make('test');
        $user->first_name = 'Limited';
        $user->last_name = 'User';
        $user->activated = 1;
        $user->save();

        $group = new Group;
        $group->name = 'Fragment group ' . uniqid();
        $group->permissions = json_encode($permissions);
        $group->save();

        $user->groups()->save($group);

        return $user;
    }

    /**
     * Sign in as the given user.
     */
    private function loginAs(User $user)
    {
        Auth::guard('redminguard')->loginUsingId($user->id);
        Auth::forgetGuards();
    }
}
