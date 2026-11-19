<?php namespace Redooor\Redminportal\Test;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;

/**
 * Permission checks must classify an action the same way the controller will use its id.
 */
class PermissablePermissionTest extends RedminBrowserTestCase
{
    private $userCount = 0;

    private function userWithPermissions(array $permissions)
    {
        $this->userCount++;

        $user = new User;
        $user->email = 'user' . $this->userCount . '@test.com';
        $user->password = Hash::make('test');
        $user->first_name = 'Original';
        $user->last_name = 'Tester';
        $user->activated = 1;
        $user->permissions = json_encode([]);
        $user->save();

        $group = new Group;
        $group->name = 'Group' . $this->userCount;
        $group->permissions = json_encode($permissions);
        $group->save();

        $user->groups()->save($group);

        return $user;
    }

    private function assertAccess($expected, User $user, $method, $uri, array $input = [])
    {
        $request = Request::create($uri, $method, $input);

        $this->assertSame(
            $expected,
            $user->hasAccess($request),
            $method . ' ' . $uri . ' with ' . json_encode($input)
        );
    }

    public function testEncodedPathDoesNotSkipAnExplicitDeny()
    {
        $user = $this->userWithPermissions(['admin' => 1, 'admin.users.update' => -1]);

        $plain = Request::create('/admin/users/store', 'POST', ['id' => '1']);
        $encoded = Request::create('/admin/%75sers/store', 'POST', ['id' => '1']);

        $this->assertSame('admin/%75sers/store', $encoded->path());
        $this->assertSame('admin/users/store', $encoded->decodedPath());

        $this->assertFalse($user->hasAccess($plain));
        $this->assertFalse($user->hasAccess($encoded));
    }

    public function testStoreUsesTheSameIdAsTheController()
    {
        $creator = $this->userWithPermissions(['admin.users.create' => 1]);
        $updater = $this->userWithPermissions(['admin.users.update' => 1]);

        $this->assertAccess(true, $creator, 'POST', '/admin/users/store');
        $this->assertAccess(true, $creator, 'POST', '/admin/users/store?id=1', ['id' => '']);
        $this->assertAccess(true, $creator, 'POST', '/admin/users/store?id=1', ['id' => '   ']);
        $this->assertAccess(false, $creator, 'POST', '/admin/users/store?id=1', ['id' => '1']);
        $this->assertAccess(false, $creator, 'POST', '/admin/users/store?id=1', ['id' => ['1']]);
        $this->assertAccess(false, $creator, 'POST', '/admin/users/store?id=1');

        $this->assertAccess(true, $updater, 'POST', '/admin/users/store?id=1', ['id' => '1']);
        $this->assertAccess(true, $updater, 'POST', '/admin/users/store', ['id' => ['1']]);
    }

    public function testStoreWithQueryIdDoesNotUpdateWhenBodyIdIsEmpty()
    {
        $admin = $this->userWithPermissions(['admin' => 1]);
        $victim = $this->userWithPermissions(['admin.users.view' => 1]);
        $role = $victim->groups()->first();

        Auth::guard('redminguard')->loginUsingId($admin->id);

        $this->call('POST', '/admin/users/store?id=' . $victim->id, [
            'id' => '',
            'first_name' => 'Changed',
            'last_name' => 'Name',
            'email' => 'changed@test.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => $role->id,
            'activated' => 1,
        ]);

        $this->assertSame('Original', $victim->fresh()->first_name);
        $this->assertSame($victim->email, $victim->fresh()->email);
    }

    public function testOrdersAndPurchasesStoreAlwaysNeedCreate()
    {
        $updater = $this->userWithPermissions([
            'admin.orders.update' => 1, 'admin.orders.create' => -1,
            'admin.purchases.update' => 1, 'admin.purchases.create' => -1,
        ]);

        foreach (['orders', 'purchases'] as $module) {
            $this->assertAccess(false, $updater, 'POST', '/admin/' . $module . '/store', ['id' => '999']);
            $this->assertAccess(false, $updater, 'POST', '/admin/' . $module . '/store?id=999');
            $this->assertAccess(false, $updater, 'POST', '/admin/' . $module . '/store');
        }

        $creator = $this->userWithPermissions([
            'admin.orders.create' => 1,
            'admin.purchases.create' => 1,
        ]);

        foreach (['orders', 'purchases'] as $module) {
            $this->assertAccess(true, $creator, 'POST', '/admin/' . $module . '/store', ['id' => '999']);
            $this->assertAccess(true, $creator, 'POST', '/admin/' . $module . '/store?id=999');
        }
    }

    public function testViewOnlyUserCannotRunMutationsClassifiedAsView()
    {
        $user = $this->userWithPermissions([
            'admin.users.view' => 1,
            'admin.products.view' => 1,
            'admin.medias.view' => 1,
            'admin.modules.view' => 1,
            'admin.orders.view' => 1,
            'admin.coupons.view' => 1,
            'admin.reports.view' => 1,
            'admin.api.view' => 1,
        ]);

        $this->assertAccess(false, $user, 'POST', '/admin/users/activate/1');
        $this->assertAccess(false, $user, 'POST', '/admin/users/deactivate/1');
        $this->assertAccess(false, $user, 'POST', '/admin/products/imgremove/1');
        $this->assertAccess(false, $user, 'POST', '/admin/products/variant-imgremove/1/1');
        $this->assertAccess(false, $user, 'GET', '/admin/medias/uploadform/1');
        $this->assertAccess(false, $user, 'POST', '/admin/medias/upload/1');
        $this->assertAccess(false, $user, 'POST', '/admin/medias/duration/1');
        $this->assertAccess(false, $user, 'POST', '/admin/products/delete-variant-json/1');
        $this->assertAccess(false, $user, 'GET', '/admin/products/create-variant/1');
        $this->assertAccess(false, $user, 'GET', '/admin/products/edit-variant/1/1');
        $this->assertAccess(false, $user, 'POST', '/admin/orders/update');
        $this->assertAccess(false, $user, 'POST', '/admin/orders/update/status/1');

        $this->assertAccess(true, $user, 'GET', '/admin/users');
        $this->assertAccess(true, $user, 'GET', '/admin/products');
        $this->assertAccess(true, $user, 'GET', '/admin/medias');
        $this->assertAccess(true, $user, 'GET', '/admin/users/sort/email/asc');
        $this->assertAccess(true, $user, 'GET', '/admin/users/search/email/test');
        $this->assertAccess(true, $user, 'GET', '/admin/orders/search-all/test');
        $this->assertAccess(true, $user, 'GET', '/admin/coupons/categories');
        $this->assertAccess(true, $user, 'GET', '/admin/modules/medias/1');
        $this->assertAccess(true, $user, 'GET', '/admin/modules/editmedias/1/1');
        $this->assertAccess(true, $user, 'GET', '/admin/api/email');
        $this->assertAccess(true, $user, 'POST', '/admin/reports/orders');
    }

    public function testUpdateUserCanRunUpdateMutations()
    {
        $user = $this->userWithPermissions([
            'admin.users.update' => 1,
            'admin.products.update' => 1,
            'admin.medias.update' => 1,
            'admin.orders.update' => 1,
        ]);

        $this->assertAccess(true, $user, 'POST', '/admin/users/activate/1');
        $this->assertAccess(true, $user, 'POST', '/admin/users/deactivate/1');
        $this->assertAccess(true, $user, 'POST', '/admin/products/imgremove/1');
        $this->assertAccess(true, $user, 'POST', '/admin/products/variant-imgremove/1/1');
        $this->assertAccess(true, $user, 'GET', '/admin/medias/uploadform/1');
        $this->assertAccess(true, $user, 'POST', '/admin/medias/upload/1');
        $this->assertAccess(true, $user, 'POST', '/admin/medias/duration/1');
        $this->assertAccess(true, $user, 'GET', '/admin/products/edit-variant/1/1');
        $this->assertAccess(true, $user, 'POST', '/admin/orders/update');
        $this->assertAccess(true, $user, 'POST', '/admin/orders/update/status/1');
    }

    public function testDeleteUserCanDeleteVariantButNotUpdateIt()
    {
        $user = $this->userWithPermissions(['admin.products.delete' => 1]);

        $this->assertAccess(true, $user, 'POST', '/admin/products/delete-variant-json/1');
        $this->assertAccess(false, $user, 'POST', '/admin/products/variant-imgremove/1/1');
    }

    public function testForcedDenyStillBlocksUpdateMutation()
    {
        $user = $this->userWithPermissions([
            'admin.users.view' => 1,
            'admin.users.update' => -1,
        ]);

        $this->assertAccess(false, $user, 'POST', '/admin/users/deactivate/1');
    }

    public function testCreateVariantUsesCreatePermission()
    {
        $creator = $this->userWithPermissions(['admin.products.create' => 1]);
        $viewer = $this->userWithPermissions(['admin.products.view' => 1]);

        $this->assertAccess(true, $creator, 'GET', '/admin/products/create-variant/1');
        $this->assertAccess(false, $viewer, 'GET', '/admin/products/create-variant/1');
    }

    public function testEditVariantUsesUpdatePermission()
    {
        $updater = $this->userWithPermissions(['admin.products.update' => 1]);
        $viewer = $this->userWithPermissions(['admin.products.view' => 1]);

        $this->assertAccess(true, $updater, 'GET', '/admin/products/edit-variant/1/1');
        $this->assertAccess(false, $viewer, 'GET', '/admin/products/edit-variant/1/1');
    }

    public function testVariantStoreUsesStoreIdRule()
    {
        $creator = $this->userWithPermissions(['admin.products.create' => 1]);
        $updater = $this->userWithPermissions(['admin.products.update' => 1]);

        $this->assertAccess(true, $creator, 'POST', '/admin/products/store', ['product_id' => 1]);
        $this->assertAccess(true, $creator, 'POST', '/admin/products/store', [
            'product_id' => 1,
            'id' => '',
        ]);
        $this->assertAccess(false, $creator, 'POST', '/admin/products/store', [
            'product_id' => 1,
            'id' => '1',
        ]);
        $this->assertAccess(true, $updater, 'POST', '/admin/products/store', [
            'product_id' => 1,
            'id' => '1',
        ]);
    }
}
