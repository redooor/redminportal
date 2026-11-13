<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;

/**
 * Checks group permission rules when storing and deleting groups.
 */
class PrivilegeGrantGroupControllerTest extends RedminBrowserTestCase
{
    private function createUser($email, array $permissions = [])
    {
        $user = new User;
        $user->email = $email;
        $user->password = Hash::make('test');
        $user->first_name = 'Group';
        $user->last_name = 'Tester';
        $user->activated = 1;
        $user->permissions = json_encode($permissions);
        $user->save();

        return $user;
    }

    private function createGroup($name, array $permissions)
    {
        $group = new Group;
        $group->name = $name;
        $group->permissions = json_encode($permissions);
        $group->save();

        return $group;
    }

    private function login(User $user)
    {
        Auth::guard('redminguard')->loginUsingId($user->id);
    }

    private function groupInput(array $overrides = [])
    {
        return array_merge([
            'name' => 'Created Group',
            'permission-inherit' => '',
            'permission-allow' => '',
            'permission-deny' => '',
        ], $overrides);
    }

    public function testActorCannotGrantGroupPermissionTheyDoNotHave()
    {
        $actor = $this->createUser('actor@test.com', ['admin.groups.create' => 1]);
        $this->login($actor);

        $this->call('POST', '/admin/groups/store', $this->groupInput([
            'permission-allow' => 'admin.view',
        ]));

        $this->assertRedirectedTo('/admin/groups/create');
        $this->assertSessionHasErrors('group_error_cannot_grant');
        $this->assertNull(Group::where('name', 'Created Group')->first());
    }

    public function testActorCanCreateGroupWithPermissionTheyHold()
    {
        $actor = $this->createUser('actor@test.com', [
            'admin.groups.create' => 1,
            'admin.users.view' => 1,
        ]);
        $this->login($actor);

        $this->call('POST', '/admin/groups/store', $this->groupInput([
            'permission-allow' => 'admin.users.view',
        ]));

        $this->assertRedirectedTo('/admin/groups');
        $this->assertNotNull(Group::where('name', 'Created Group')->first());
    }

    public function testActorCannotEditGroupWithHigherPermissions()
    {
        $actor = $this->createUser('actor@test.com', ['admin.groups.update' => 1]);
        $target = $this->createGroup('Higher Group', ['admin.view' => 1]);
        $this->login($actor);

        $this->call('POST', '/admin/groups/store', $this->groupInput([
            'id' => $target->id,
            'name' => 'Changed Group',
        ]));

        $this->assertRedirectedTo('/admin/groups/edit/' . $target->id);
        $this->assertSessionHasErrors('group_error_cannot_grant');
        $this->assertSame('Higher Group', $target->fresh()->name);
    }

    public function testActorCannotDeleteGroupWithHigherPermissions()
    {
        $actor = $this->createUser('actor@test.com', ['admin.groups.delete' => 1]);
        $target = $this->createGroup('Higher Group', ['admin.view' => 1]);
        $this->login($actor);

        $this->call('POST', '/admin/groups/delete/' . $target->id);

        $this->assertSessionHasErrors('group_error_cannot_grant');
        $this->assertNotNull(Group::find($target->id));
    }

    public function testFullAdminCanCreateGroupWithAdminPermission()
    {
        $actor = $this->createUser('full@test.com', [
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
            'admin.delete' => 1,
        ]);
        $this->login($actor);

        $this->call('POST', '/admin/groups/store', $this->groupInput([
            'permission-allow' => 'admin.view',
        ]));

        $this->assertRedirectedTo('/admin/groups');
        $created = Group::where('name', 'Created Group')->first();
        $this->assertNotNull($created);
        $this->assertSame(1, json_decode($created->permissions)->{'admin.view'});
    }

    public function testActorCannotLiftGroupDenyTheyAreDenied()
    {
        $actor = $this->createUser('actor@test.com', ['admin.groups.update' => 1]);
        $allowGroup = $this->createGroup('Pages Allow', ['admin.pages.view' => 1]);
        $denyGroup = $this->createGroup('Pages Deny', ['admin.pages.view' => -1]);
        $actor->groups()->save($allowGroup);
        $actor->groups()->save($denyGroup);
        $this->login($actor);

        $this->call('POST', '/admin/groups/store', $this->groupInput([
            'id' => $denyGroup->id,
            'name' => 'Pages Deny',
        ]));

        $this->assertRedirectedTo('/admin/groups/edit/' . $denyGroup->id);
        $this->assertSessionHasErrors('group_error_cannot_grant');
        $this->assertSame(-1, json_decode($denyGroup->fresh()->permissions)->{'admin.pages.view'});
    }

    public function testFullAdminCanLiftGroupDeny()
    {
        $actor = $this->createUser('full@test.com', [
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
            'admin.delete' => 1,
        ]);
        $target = $this->createGroup('Pages Deny', ['admin.pages.view' => -1]);
        $this->login($actor);

        $this->call('POST', '/admin/groups/store', $this->groupInput([
            'id' => $target->id,
            'name' => 'Pages Deny',
        ]));

        $this->assertRedirectedTo('/admin/groups');
        $this->assertSame([], (array) json_decode($target->fresh()->permissions, true));
    }
}
