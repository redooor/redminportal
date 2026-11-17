<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;

/**
 * Covers atomic user save and group assignment.
 */
class UserSaveAtomicFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/users';
        $this->viewhas = array(
            'singular' => 'user',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => $this->userInput(),
            'edit' => $this->userInput(array(
                'id' => 2,
                'email' => 'edited@example.com'
            ))
        );
    }

    /**
     * The seeded admin has id 1, so use a truly missing id.
     */
    public function testEditFail404()
    {
        $this->call('GET', $this->page . '/edit/999');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }

    /**
     * Test (Fail): an unknown role on create does not save the user row
     */
    public function testCreateWithUnknownRoleLeavesNoNewRow()
    {
        $this->postStore($this->userInput(array(
            'email' => 'unknown-role@example.com',
            'role' => 999
        )));

        $this->assertRedirectedTo('/admin/users/create');
        $this->assertErrorMessage(
            'groupError',
            'The user cannot be updated because the selected group cannot be found. Please try again.'
        );
        $this->assertNull(User::where('email', 'unknown-role@example.com')->first());
        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): an unknown role on update keeps the original user and groups
     */
    public function testUpdateWithUnknownRoleKeepsOriginalUserAndGroups()
    {
        $user = $this->createUser('target@example.com', array('admin.users.view' => 1));
        $group = $this->createGroup('Original Group');
        $user->groups()->save($group);

        $this->postStore($this->userInput(array(
            'id' => $user->id,
            'first_name' => 'Changed',
            'last_name' => 'Person',
            'email' => 'changed@example.com',
            'role' => 999,
            'permission-allow' => 'admin.pages.view'
        )));

        $this->assertRedirectedTo('/admin/users/edit/' . $user->id);
        $this->assertErrorMessage(
            'groupError',
            'The user cannot be updated because the selected group cannot be found. Please try again.'
        );

        $user = User::find($user->id);
        $this->assertSame('target@example.com', $user->email);
        $this->assertSame('Original', $user->first_name);
        $this->assertSame('Tester', $user->last_name);
        $this->assertSame(
            array('admin.users.view' => 1),
            (array) json_decode($user->permissions, true)
        );
        $this->assertSame(array($group->id), $this->groupIds($user));
    }

    /**
     * Test (Fail): no selected role keeps the groupError and rolls the fields back
     */
    public function testUpdateWithoutRoleKeepsGroupErrorAndRollsBack()
    {
        $user = $this->createUser('no-role@example.com', array('admin.users.view' => 1));
        $group = $this->createGroup('Original Group');
        $user->groups()->save($group);

        // The role field is required, so an unselected multi-select arrives as an
        // array with a null entry. addGroup() treats that as no role and returns false.
        $this->postStore($this->userInput(array(
            'id' => $user->id,
            'first_name' => 'Changed',
            'last_name' => 'Person',
            'email' => 'changed@example.com',
            'role' => array(null),
            'permission-allow' => 'admin.pages.view'
        )));

        $this->assertRedirectedTo('/admin/users/edit/' . $user->id);
        $this->assertErrorMessage(
            'groupError',
            'The user cannot be updated because the selected group cannot be found. Please try again.'
        );

        $user = User::find($user->id);
        $this->assertSame('no-role@example.com', $user->email);
        $this->assertSame('Original', $user->first_name);
        $this->assertSame('Tester', $user->last_name);
        $this->assertSame(
            array('admin.users.view' => 1),
            (array) json_decode($user->permissions, true)
        );
        $this->assertSame(array($group->id), $this->groupIds($user));
    }

    /**
     * Test (Pass): valid create and update still work with one role
     */
    public function testValidCreateAndUpdateWithOneRole()
    {
        $this->postStore($this->userInput(array(
            'email' => 'one-role@example.com',
            'role' => 1
        )));

        $this->assertRedirectedTo('/admin/users');
        $user = User::where('email', 'one-role@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('New', $user->first_name);
        $this->assertSame(array(1), $this->groupIds($user));

        $this->postStore($this->userInput(array(
            'id' => $user->id,
            'first_name' => 'Updated',
            'email' => 'one-role@example.com',
            'role' => 2
        )));

        $this->assertRedirectedTo('/admin/users');
        $user = User::find($user->id);
        $this->assertSame('Updated', $user->first_name);
        $this->assertSame(array(2), $this->groupIds($user));
    }

    /**
     * Test (Pass): valid create and update still work with several roles
     */
    public function testValidCreateAndUpdateWithSeveralRoles()
    {
        $this->postStore($this->userInput(array(
            'email' => 'several-roles@example.com',
            'role' => array(1, 2)
        )));

        $this->assertRedirectedTo('/admin/users');
        $user = User::where('email', 'several-roles@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(array(1, 2), $this->groupIds($user));

        $replacement = $this->createGroup('Replacement Group');

        $this->postStore($this->userInput(array(
            'id' => $user->id,
            'first_name' => 'Updated',
            'email' => 'several-roles@example.com',
            'role' => array(2, $replacement->id)
        )));

        $this->assertRedirectedTo('/admin/users');
        $user = User::find($user->id);
        $this->assertSame('Updated', $user->first_name);
        $this->assertSame(array(2, $replacement->id), $this->groupIds($user));
    }

    /**
     * A valid user payload, with optional overrides
     */
    private function userInput(array $overrides = array())
    {
        return array_merge(array(
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'created@example.com',
            'password' => 'Passw0rd-1234',
            'password_confirmation' => 'Passw0rd-1234',
            'role' => 1,
            'activated' => 1,
            'permission-inherit' => '',
            'permission-allow' => '',
            'permission-deny' => ''
        ), $overrides);
    }

    /**
     * Create a saved user fixture
     */
    private function createUser($email, array $permissions = array())
    {
        return $this->createModel(User::class, array(
            'email' => $email,
            'password' => Hash::make('Passw0rd-1234'),
            'first_name' => 'Original',
            'last_name' => 'Tester',
            'activated' => 1,
            'permissions' => json_encode($permissions)
        ));
    }

    /**
     * Create a saved group fixture
     */
    private function createGroup($name)
    {
        return $this->createModel(Group::class, array(
            'name' => $name,
            'permissions' => json_encode(array())
        ));
    }

    /**
     * Group ids ordered by group id
     */
    private function groupIds(User $user)
    {
        return $user->groups()->orderBy('groups.id')->pluck('groups.id')->all();
    }
}
