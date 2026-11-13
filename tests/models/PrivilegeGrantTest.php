<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;

/**
 * Checks which permission entries an account may hand out.
 */
class PrivilegeGrantTest extends RedminBrowserTestCase
{
    private $userCount = 0;

    private function userWithPermissions(array $permissions)
    {
        $this->userCount++;

        $user = new User;
        $user->email = 'grantuser' . $this->userCount . '@test.com';
        $user->password = Hash::make('test');
        $user->first_name = 'Grant';
        $user->last_name = 'Tester';
        $user->activated = 1;
        $user->permissions = json_encode($permissions);
        $user->save();

        return $user;
    }

    private function groupWithPermissions($name, array $permissions)
    {
        $group = new Group;
        $group->name = $name;
        $group->permissions = json_encode($permissions);
        $group->save();

        return $group;
    }

    public function testHasPermissionUsesUserAndGroupPermissions()
    {
        $user = $this->userWithPermissions([
            'admin.users.create' => 1,
            'admin.users.update' => 1,
        ]);
        $group = $this->groupWithPermissions('Pages', ['admin.pages.view' => 1]);
        $user->groups()->save($group);

        $this->assertTrue($user->hasPermission('admin/users', 'create'));
        $this->assertTrue($user->hasPermission('admin/users', 'update'));
        $this->assertFalse($user->hasPermission('admin/users', 'view'));
        $this->assertFalse($user->hasPermission('admin/users', 'delete'));
        $this->assertTrue($user->hasPermission('admin/pages', 'view'));
        $this->assertFalse($user->hasPermission('admin/pages', 'create'));
    }

    public function testCanGrantOnlyPermissionsHeldByActor()
    {
        $user = $this->userWithPermissions([
            'admin.users.create' => 1,
            'admin.users.update' => 1,
        ]);

        $this->assertTrue($user->canGrant([
            'admin.users.create' => 1,
            'admin.users.update' => 1,
        ]));
        $this->assertFalse($user->canGrant(['admin.pages.view' => 1]));
        $this->assertFalse($user->canGrant(['admin.users.view' => 1]));
    }

    public function testCanGrantAllowsInheritAndDenyValues()
    {
        $user = $this->userWithPermissions(['admin.users.create' => 1]);

        $this->assertTrue($user->canGrant([
            'admin.pages.view' => 0,
            'admin.pages.create' => -1,
            'admin.pages.update' => -1,
        ]));
    }

    public function testLegacyAdminKeyNeedsAllFourActions()
    {
        $partial = $this->userWithPermissions([
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
        ]);

        $this->assertFalse($partial->canGrant(['admin' => 1]));

        $full = $this->userWithPermissions([
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
            'admin.delete' => 1,
        ]);

        $this->assertTrue($full->canGrant(['admin' => 1]));
    }

    public function testLegacyPathKeyNeedsAllFourActions()
    {
        $user = $this->userWithPermissions([
            'admin.users.view' => 1,
            'admin.users.create' => 1,
            'admin.users.update' => 1,
        ]);

        $this->assertFalse($user->canGrant(['admin.users' => 1]));
    }

    public function testCoversUserUsesPositiveUserAndGroupPermissions()
    {
        $actor = $this->userWithPermissions([
            'admin.users.create' => 1,
            'admin.users.update' => 1,
        ]);

        $target = $this->userWithPermissions(['admin.users.create' => 1]);
        $targetGroup = $this->groupWithPermissions('Users', ['admin.users.update' => 1]);
        $target->groups()->save($targetGroup);

        $this->assertTrue($actor->coversUser($target));
        $this->assertTrue($actor->coversUser($actor));

        $target->groups()->detach();
        $higherGroup = $this->groupWithPermissions('Pages', ['admin.pages.view' => 1]);
        $target->groups()->save($higherGroup);

        $this->assertFalse($actor->coversUser($target->fresh()));
    }

    public function testCoversUserIgnoresNonPositiveTargetPermissions()
    {
        $actor = $this->userWithPermissions(['admin.users.create' => 1]);

        $target = $this->userWithPermissions([
            'admin.pages.view' => 0,
            'admin.pages.create' => -1,
        ]);

        $this->assertTrue($actor->coversUser($target));
    }

    public function testCanGrantFollowsGroupDeny()
    {
        $user = $this->userWithPermissions([]);
        $allowGroup = $this->groupWithPermissions('Allow', ['admin.users.create' => 1]);
        $denyGroup = $this->groupWithPermissions('Deny', ['admin.users.create' => -1]);
        $user->groups()->save($allowGroup);
        $user->groups()->save($denyGroup);

        $this->assertFalse($user->canGrant(['admin.users.create' => 1]));
    }

    public function testCanGrantRejectsNarrowerDeniesInUserAndGroupPermissions()
    {
        $user = $this->userWithPermissions([
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
            'admin.delete' => 1,
            'admin.pages.view' => -1,
        ]);

        $this->assertFalse($user->canGrant(['admin.view' => 1]));
        $this->assertFalse($user->canGrant(['admin' => 1]));

        $groupUser = $this->userWithPermissions([]);
        $allowGroup = $this->groupWithPermissions('Allow', [
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
            'admin.delete' => 1,
        ]);
        $denyGroup = $this->groupWithPermissions('Deny', ['admin.pages.view' => -1]);
        $groupUser->groups()->save($allowGroup);
        $groupUser->groups()->save($denyGroup);

        $this->assertFalse($groupUser->canGrant(['admin.view' => 1]));
        $this->assertFalse($groupUser->canGrant(['admin' => 1]));
    }

    public function testCoversUserRejectsUnrestrictedAdministratorWhenActorHasNarrowerDeny()
    {
        $actor = $this->userWithPermissions([
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
            'admin.delete' => 1,
            'admin.pages.view' => -1,
        ]);
        $target = $this->userWithPermissions(['admin' => 1]);

        $this->assertFalse($actor->coversUser($target));
    }

    public function testLiftedDeniesReturnsOnlyEntriesNoLongerDenied()
    {
        $user = $this->userWithPermissions([]);
        $old = [
            'admin.users.create' => -1,
            'admin.users.update' => -1,
            'admin.users.view' => 1,
            'admin.users.delete' => -1,
            'admin.pages.view' => -1,
        ];
        $new = [
            'admin.users.create' => 1,
            'admin.users.update' => -1,
            'admin.users.view' => 0,
            'admin.users.delete' => 0,
        ];

        $this->assertSame([
            'admin.users.create' => 1,
            'admin.users.delete' => 1,
            'admin.pages.view' => 1,
        ], $user->liftedDenies($old, $new));
    }

    public function testUserAllowThatOverridesGroupDenyDoesNotBlockGrant()
    {
        $user = $this->userWithPermissions(['admin' => 1]);
        $user->groups()->save($this->groupWithPermissions('Pages denied', ['admin.pages.view' => -1]));

        $this->assertTrue($user->hasPermission('admin/pages', 'view'));
        $this->assertTrue($user->canGrant(['admin' => 1]));
        $this->assertTrue($user->canGrant(['admin.view' => 1]));
    }
}
