<?php namespace Redooor\Redminportal\Test;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;

/**
 * The "redmin" gate answers from the same stored permissions as the route middleware. A host site that keeps its members
 * in the same users table (group {"user":1}) must never be treated as an admin by it.
 */
class RedminGateTest extends RedminTestCase
{
    private function group($name, array $permissions)
    {
        $group = new Group;
        $group->name = $name;
        $group->permissions = json_encode($permissions);
        $group->save();

        return $group;
    }

    private function user($email, Group $group = null, $activated = 1)
    {
        $user = new User;
        $user->email = $email;
        $user->password = Hash::make('secret');
        $user->first_name = 'First';
        $user->last_name = 'Last';
        $user->activated = $activated;
        $user->save();

        if ($group) {
            $user->groups()->save($group);
        }

        return User::find($user->id);
    }

    private function signInAdmin($permissions = ['admin' => 1])
    {
        $admin = $this->user('admin@test.com', $this->group('Admin', $permissions));
        Auth::guard('redminguard')->loginUsingId($admin->id);

        return $admin;
    }

    private function member()
    {
        return $this->user('member@test.com', $this->group('User', ['user' => 1]));
    }

    public function testSignedInAdminIsAllowedWhatTheirGroupAllows()
    {
        $this->signInAdmin();

        $this->assertTrue(Gate::allows('redmin', ['admin/pages', 'update']));
        $this->assertTrue(Gate::allows('redmin', ['admin/pages', 'delete']));
        $this->assertTrue(Gate::allows('redmin', 'admin/pages'), 'The action defaults to view');
    }

    public function testSignedInAdminIsDeniedWhatTheirGroupDoesNotAllow()
    {
        $this->signInAdmin(['admin.pages.view' => 1, 'admin.pages.update' => 1, 'admin.pages.delete' => -1]);

        $this->assertTrue(Gate::allows('redmin', ['admin/pages', 'update']));
        $this->assertFalse(Gate::allows('redmin', ['admin/pages', 'delete']));
        $this->assertFalse(Gate::allows('redmin', ['admin/users', 'view']));
    }

    public function testGuestIsDenied()
    {
        $this->assertTrue(Auth::guard('redminguard')->guest());
        $this->assertFalse(Gate::allows('redmin', ['admin/pages', 'view']));
        $this->assertTrue(Gate::denies('redmin', ['admin/pages', 'view']));
    }

    public function testMemberGroupIsDeniedEveryAdminPathOnTheAdminGuard()
    {
        Auth::guard('redminguard')->loginUsingId($this->member()->id);

        foreach (['admin', 'admin/dashboard', 'admin/users', 'admin/orders', 'myaccount', 'api/tag'] as $path) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                $this->assertFalse(Gate::allows('redmin', [$path, $action]), "$path $action");
            }
        }
    }

    public function testMemberOnAnotherGuardIsEvaluatedByTheirOwnPermissions()
    {
        // A host that signs members in on its default guard passes that user to the Gate
        $member = $this->member();

        $this->assertFalse(Gate::forUser($member)->allows('redmin', ['admin/pages', 'view']));
        $this->assertFalse(Gate::forUser($member)->allows('redmin', ['admin/users', 'update']));
    }

    public function testMemberOnTheDefaultGuardIsNotUpgradedByAnAdminOnTheAdminGuard()
    {
        $this->signInAdmin();
        $member = $this->member();

        $this->assertFalse(Gate::forUser($member)->allows('redmin', ['admin/pages', 'view']));
        $this->assertTrue(Gate::allows('redmin', ['admin/pages', 'view']));
    }

    public function testAdminPassedToTheGateIsAllowed()
    {
        $admin = $this->user('admin@test.com', $this->group('Admin', ['admin' => 1]));

        $this->assertTrue(Gate::forUser($admin)->allows('redmin', ['admin/pages', 'update']));
    }

    public function testUserThatIsNotARedminUserIsDeniedEvenWithAnAdminSignedIn()
    {
        $other = new class implements Authenticatable {
            public function getAuthIdentifierName() { return 'id'; }
            public function getAuthIdentifier() { return 99; }
            public function getAuthPasswordName() { return 'password'; }
            public function getAuthPassword() { return ''; }
            public function getRememberToken() { return null; }
            public function setRememberToken($value) {}
            public function getRememberTokenName() { return ''; }
        };

        $this->assertFalse(Gate::forUser($other)->allows('redmin', ['admin/pages', 'view']));

        // The gate answers for the user it was given, not for an admin who happens to be signed in on the admin guard
        $this->signInAdmin();

        $this->assertFalse(Gate::forUser($other)->allows('redmin', ['admin/pages', 'view']));
        $this->assertTrue(Gate::allows('redmin', ['admin/pages', 'view']));
    }

    public function testDeactivatedUserIsDenied()
    {
        $user = $this->user('off@test.com', $this->group('Admin', ['admin' => 1]), 0);

        $this->assertFalse(Gate::forUser($user)->allows('redmin', ['admin/pages', 'view']));
    }

    public function testUnknownActionIsDenied()
    {
        $this->signInAdmin();

        $this->assertFalse(Gate::allows('redmin', ['admin/pages', 'publish']));
        $this->assertFalse(Gate::allows('redmin', ['admin/pages', '']));
    }

    public function testSurroundingSlashesInThePathAreIgnored()
    {
        $this->signInAdmin(['admin.pages.view' => 1]);

        $this->assertTrue(Gate::allows('redmin', ['/admin/pages/', 'view']));
        $this->assertFalse(Gate::allows('redmin', ['/admin/users/', 'view']));
    }

    public function testEncodedAndOddPathsDoNotSlipPastADeny()
    {
        $admin = $this->signInAdmin(['admin.view' => 1, 'admin.users.view' => -1]);

        $this->assertFalse($admin->hasAccess(Request::create('admin/%75sers', 'GET')));
        $this->assertFalse(Gate::allows('redmin', ['admin/%75sers', 'view']));
        $this->assertFalse(Gate::allows('redmin', ['admin/users', 'view']));
        $this->assertFalse(Gate::allows('redmin', ['admin//users', 'view']));
        $this->assertFalse(Gate::allows('redmin', ['admin/../admin/users', 'view']));
        $this->assertFalse(Gate::allows('redmin', ['', 'view']));
        $this->assertTrue(Gate::allows('redmin', ['admin/pages', 'view']));
        $this->assertTrue(Gate::allows('redmin', ['admin//pages', 'view']));
    }

    public function testGateAgreesWithTheRouteCheck()
    {
        $admin = $this->signInAdmin(['admin.pages.view' => 1, 'admin.pages.create' => 1, 'admin.pages.delete' => -1, 'admin.posts.view' => 1]);

        $cases = [
            ['GET', 'admin/pages', 'view'],
            ['POST', 'admin/pages/store', 'create'],
            ['POST', 'admin/pages/delete/1', 'delete'],
            ['GET', 'admin/posts', 'view'],
            ['POST', 'admin/posts/store', 'create'],
            ['GET', 'admin/users', 'view'],
        ];

        foreach ($cases as [$method, $path, $action]) {
            $this->assertSame(
                $admin->hasAccess(Request::create($path, $method)),
                $admin->hasPermission(preg_replace('#/(store|delete/\d+)$#', '', $path), $action),
                "$method $path"
            );
        }

        $this->assertTrue(Gate::allows('redmin', ['admin/pages', 'create']));
        $this->assertFalse(Gate::allows('redmin', ['admin/pages', 'delete']));
        $this->assertTrue(Gate::allows('redmin', ['admin/posts', 'view']));
        $this->assertFalse(Gate::allows('redmin', ['admin/posts', 'create']));
        $this->assertFalse(Gate::allows('redmin', ['admin/users', 'view']));
    }

    public function testCanDirectiveUsesTheGate()
    {
        $this->signInAdmin(['admin.pages.update' => 1, 'admin.pages.delete' => -1]);

        $template = "@can('redmin', ['admin/pages', 'update'])\nUPDATE\n@endcan\n"
            . "@can('redmin', ['admin/pages', 'delete'])\nDELETE\n@endcan\n"
            . "@cannot('redmin', ['admin/users', 'view'])\nNOUSERS\n@endcannot\n";

        $html = Blade::render($template);

        $this->assertSame('UPDATE NOUSERS', trim(preg_replace('/\\s+/', ' ', $html)));
    }
}
