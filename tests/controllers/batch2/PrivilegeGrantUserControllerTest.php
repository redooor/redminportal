<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;

/**
 * Checks account and group assignment rules when storing users.
 */
class PrivilegeGrantUserControllerTest extends RedminBrowserTestCase
{
    private function createUser($email, array $permissions = [])
    {
        $user = new User;
        $user->email = $email;
        $user->password = Hash::make('test');
        $user->first_name = 'Original';
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

    private function userInput($role, array $overrides = [])
    {
        return array_merge([
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'created@test.com',
            'password' => 'Passw0rd-1234',
            'password_confirmation' => 'Passw0rd-1234',
            'role' => $role,
            'activated' => 1,
            'permission-inherit' => '',
            'permission-allow' => '',
            'permission-deny' => '',
        ], $overrides);
    }

    public function testActorCannotGrantPermissionTheyDoNotHave()
    {
        $actor = $this->createUser('actor@test.com', [
            'admin.users.create' => 1,
            'admin.users.update' => 1,
        ]);
        $coveredGroup = $this->createGroup('Covered', ['admin.users.create' => 1]);
        $this->login($actor);

        $this->call('POST', '/admin/users/store', $this->userInput($coveredGroup->id, [
            'email' => 'blocked@test.com',
            'permission-allow' => 'admin.pages.view',
        ]));

        $this->assertRedirectedTo('/admin/users/create');
        $this->assertSessionHasErrors('user_error_cannot_grant');
        $this->assertNull(User::where('email', 'blocked@test.com')->first());
    }

    public function testActorCannotAssignGroupTheyDoNotCover()
    {
        $actor = $this->createUser('actor@test.com', [
            'admin.users.create' => 1,
            'admin.users.update' => 1,
        ]);
        $adminGroup = $this->createGroup('Admin', [
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
            'admin.delete' => 1,
        ]);
        $this->login($actor);

        $this->call('POST', '/admin/users/store', $this->userInput($adminGroup->id, [
            'email' => 'blocked@test.com',
        ]));

        $this->assertRedirectedTo('/admin/users/create');
        $this->assertSessionHasErrors('user_error_cannot_grant');
        $this->assertNull(User::where('email', 'blocked@test.com')->first());
    }

    public function testActorCanAssignGroupTheyCover()
    {
        $actor = $this->createUser('actor@test.com', [
            'admin.users.create' => 1,
            'admin.users.update' => 1,
        ]);
        $coveredGroup = $this->createGroup('Covered', [
            'admin.users.create' => 1,
            'admin.users.update' => 1,
        ]);
        $this->login($actor);

        $this->call('POST', '/admin/users/store', $this->userInput($coveredGroup->id));

        $this->assertRedirectedTo('/admin/users');
        $created = User::where('email', 'created@test.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->groups()->where('groups.id', $coveredGroup->id)->exists());
    }

    public function testActorCanGrantInheritAndDenyValues()
    {
        $actor = $this->createUser('actor@test.com', [
            'admin.users.create' => 1,
            'admin.users.update' => 1,
        ]);
        $coveredGroup = $this->createGroup('Covered', ['admin.users.create' => 1]);
        $this->login($actor);

        $this->call('POST', '/admin/users/store', $this->userInput($coveredGroup->id, [
            'permission-inherit' => 'admin.pages.view',
            'permission-deny' => 'admin.pages.create',
        ]));

        $this->assertRedirectedTo('/admin/users');
        $created = User::where('email', 'created@test.com')->first();
        $this->assertNotNull($created);

        $permissions = (array) json_decode($created->permissions);
        $this->assertSame(0, $permissions['admin.pages.view']);
        $this->assertSame(-1, $permissions['admin.pages.create']);
    }

    public function testActorCannotEditAccountWithHigherAccess()
    {
        $actor = $this->createUser('actor@test.com', ['admin.users.update' => 1]);
        $coveredGroup = $this->createGroup('Covered', ['admin.users.update' => 1]);
        $target = $this->createUser('target@test.com');
        $adminGroup = $this->createGroup('Admin', [
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
            'admin.delete' => 1,
        ]);
        $target->groups()->save($adminGroup);
        $this->login($actor);

        $this->call('POST', '/admin/users/store', $this->userInput($coveredGroup->id, [
            'id' => $target->id,
            'first_name' => 'Changed',
            'email' => $target->email,
            'role' => $adminGroup->id,
        ]));

        $this->assertRedirectedTo('/admin/users/edit/' . $target->id);
        $this->assertSessionHasErrors('user_error_protected_account');
        $this->assertSame('Original', $target->fresh()->first_name);
    }

    public function testActorCannotDeactivateAccountWithHigherAccess()
    {
        $actor = $this->createUser('actor@test.com', ['admin.users.update' => 1]);
        $target = $this->createUser('target@test.com');
        $adminGroup = $this->createGroup('Admin', ['admin.view' => 1]);
        $target->groups()->save($adminGroup);
        $this->login($actor);

        $this->call('POST', '/admin/users/deactivate/' . $target->id);

        $this->assertSessionHasErrors('user_error_protected_account');
        $this->assertSame(1, $target->fresh()->activated);
    }

    public function testActorCannotDeleteAccountWithHigherAccess()
    {
        $actor = $this->createUser('actor@test.com', [
            'admin.users.update' => 1,
            'admin.users.delete' => 1,
        ]);
        $target = $this->createUser('target@test.com');
        $adminGroup = $this->createGroup('Admin', ['admin.view' => 1]);
        $target->groups()->save($adminGroup);
        $this->login($actor);

        $this->call('POST', '/admin/users/delete/' . $target->id);

        $this->assertSessionHasErrors('user_error_protected_account');
        $this->assertNotNull(User::find($target->id));
    }

    public function testActorCanEditOwnAccountWithoutRaisingPermissions()
    {
        $actor = $this->createUser('actor@test.com', ['admin.users.update' => 1]);
        $staffGroup = $this->createGroup('Staff', ['admin.users.update' => 1]);
        $actor->groups()->save($staffGroup);
        $this->login($actor);

        $this->call('POST', '/admin/users/store', $this->userInput($staffGroup->id, [
            'id' => $actor->id,
            'first_name' => 'Changed',
            'email' => $actor->email,
            'permission-allow' => 'admin.users.update',
        ]));

        $this->assertRedirectedTo('/admin/users');
        $this->assertSame('Changed', $actor->fresh()->first_name);
    }

    public function testActorCannotLiftUserLevelDenyTheyAreDenied()
    {
        $actor = $this->createUser('actor@test.com', ['admin.users.update' => 1]);
        $allowGroup = $this->createGroup('Pages Allow', ['admin.pages.view' => 1]);
        $denyGroup = $this->createGroup('Pages Deny', ['admin.pages.view' => -1]);
        $actor->groups()->save($allowGroup);
        $actor->groups()->save($denyGroup);

        $target = $this->createUser('target@test.com', ['admin.pages.view' => -1]);
        $replacement = $this->createGroup('Staff', []);
        $this->login($actor);

        $this->call('POST', '/admin/users/store', $this->userInput($replacement->id, [
            'id' => $target->id,
            'email' => $target->email,
        ]));

        $this->assertRedirectedTo('/admin/users/edit/' . $target->id);
        $this->assertSessionHasErrors('user_error_cannot_grant');
        $this->assertSame(-1, json_decode($target->fresh()->permissions)->{'admin.pages.view'});
    }

    public function testFullAdminCanLiftUserLevelDeny()
    {
        $actor = $this->createUser('full@test.com', [
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
            'admin.delete' => 1,
        ]);
        $target = $this->createUser('target@test.com', ['admin.pages.view' => -1]);
        $replacement = $this->createGroup('Staff', []);
        $this->login($actor);

        $this->call('POST', '/admin/users/store', $this->userInput($replacement->id, [
            'id' => $target->id,
            'email' => $target->email,
        ]));

        $this->assertRedirectedTo('/admin/users');
        $this->assertSame([], (array) json_decode($target->fresh()->permissions, true));
    }

    public function testActorCannotRemoveDenyGroupFromAnotherUser()
    {
        $actor = $this->createUser('actor@test.com', ['admin.users.update' => 1]);
        $allowGroup = $this->createGroup('Pages Allow', ['admin.pages.view' => 1]);
        $denyGroup = $this->createGroup('Pages Deny', ['admin.pages.view' => -1]);
        $actor->groups()->save($allowGroup);
        $actor->groups()->save($denyGroup);

        $target = $this->createUser('target@test.com');
        $targetDeny = $this->createGroup('Target Deny', ['admin.pages.view' => -1]);
        $target->groups()->save($targetDeny);
        $replacement = $this->createGroup('Staff', []);
        $this->login($actor);

        $this->call('POST', '/admin/users/store', $this->userInput($replacement->id, [
            'id' => $target->id,
            'email' => $target->email,
            'role' => $replacement->id,
        ]));

        $this->assertRedirectedTo('/admin/users/edit/' . $target->id);
        $this->assertSessionHasErrors('user_error_cannot_grant');
        $this->assertTrue($target->fresh()->groups()->where('groups.id', $targetDeny->id)->exists());
    }

    public function testFullAdminCanRemoveDenyGroupFromAnotherUser()
    {
        $actor = $this->createUser('full@test.com', [
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
            'admin.delete' => 1,
        ]);
        $target = $this->createUser('target@test.com');
        $targetDeny = $this->createGroup('Target Deny', ['admin.pages.view' => -1]);
        $target->groups()->save($targetDeny);
        $replacement = $this->createGroup('Staff', []);
        $this->login($actor);

        $this->call('POST', '/admin/users/store', $this->userInput($replacement->id, [
            'id' => $target->id,
            'email' => $target->email,
            'role' => $replacement->id,
        ]));

        $this->assertRedirectedTo('/admin/users');
        $this->assertFalse($target->fresh()->groups()->where('groups.id', $targetDeny->id)->exists());
        $this->assertTrue($target->fresh()->groups()->where('groups.id', $replacement->id)->exists());
    }

    public function testFullAdminCanCreateAndManageUsers()
    {
        $actor = $this->createUser('full@test.com', [
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
            'admin.delete' => 1,
        ]);
        $adminGroup = $this->createGroup('Admin', [
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.update' => 1,
            'admin.delete' => 1,
        ]);
        $staffGroup = $this->createGroup('Staff', ['admin.users.update' => 1]);
        $this->login($actor);

        $this->call('POST', '/admin/users/store', $this->userInput($adminGroup->id, [
            'permission-allow' => 'admin.pages.view',
        ]));

        $this->assertRedirectedTo('/admin/users');
        $created = User::where('email', 'created@test.com')->first();
        $this->assertNotNull($created);

        $this->call('POST', '/admin/users/store', $this->userInput($staffGroup->id, [
            'id' => $created->id,
            'first_name' => 'Changed',
            'email' => $created->email,
            'role' => $staffGroup->id,
        ]));

        $this->assertRedirectedTo('/admin/users');
        $this->assertSame('Changed', $created->fresh()->first_name);

        $this->call('POST', '/admin/users/deactivate/' . $created->id);
        $this->assertSame(0, $created->fresh()->activated);

        $this->call('POST', '/admin/users/activate/' . $created->id);
        $this->assertSame(1, $created->fresh()->activated);

        $this->call('POST', '/admin/users/delete/' . $created->id);
        $this->assertNull(User::find($created->id));
    }
}
