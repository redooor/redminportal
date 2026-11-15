<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class GroupCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/groups';
        $this->viewhas = array(
            'singular' => 'group',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'name' => 'This is a group name',
                'permission-inherit' => 'admin.view',
                'permission-allow' => 'admin.create,admin.update',
                'permission-deny' => 'admin.delete'
            ),
            'edit' => array(
                'id' => 3,
                'name' => 'This is another group name',
                'permission-inherit' => 'admin.view',
                'permission-allow' => 'admin.create,admin.update',
                'permission-deny' => 'admin.delete'
            )
        );
    }

    /**
     * The seeded Admin group has id 1, so use a truly missing id.
     */
    public function testEditFail404()
    {
        $this->call('GET', $this->page . '/edit/999');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }

    private function createUser($email, array $permissions = array())
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

    private function createGroup($name, array $permissions = array())
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

    private function groupInput(array $overrides = array())
    {
        return array_merge(array(
            'name' => 'Created Group',
            'permission-inherit' => '',
            'permission-allow' => '',
            'permission-deny' => ''
        ), $overrides);
    }

    /**
     * Test (Pass): create then update keeps the row count and saves name and permissions
     */
    public function testCharacterizationCreateThenUpdate()
    {
        $this->postStore($this->groupInput(array(
            'name' => 'Original Group',
            'permission-allow' => 'admin.users.view'
        )));

        $this->assertRedirectedTo('/admin/groups');
        $this->assertSame(3, Group::count());

        $group = Group::where('name', 'Original Group')->first();
        $this->assertNotNull($group);
        $this->assertSame('Original Group', $group->name);
        $this->assertSame(array('admin.users.view' => 1), (array) $group->permissions());

        $this->postStore($this->groupInput(array(
            'id' => $group->id,
            'name' => 'Updated Group',
            'permission-deny' => 'admin.users.view'
        )));

        $this->assertRedirectedTo('/admin/groups');
        $this->assertSame(3, Group::count());

        $group = Group::find($group->id);
        $this->assertSame('Updated Group', $group->name);
        $this->assertSame(array('admin.users.view' => -1), (array) $group->permissions());
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/groups/create',
            $this->groupInput(array('name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/groups/edit/5',
            $this->groupInput(array('id' => '5', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/groups/create',
            $this->groupInput(array('id' => '', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is present and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/groups/edit/0',
            $this->groupInput(array('id' => '0', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $group = $this->createGroup('Existing Group');

        $this->assertValidationFailure(
            '/admin/groups/edit/' . $group->id,
            $this->groupInput(array('id' => $group->id, 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): valid input with a nonexistent id gives the editError not-found message
     */
    public function testCharacterizationNonexistentIdWithValidFields()
    {
        $this->postStore($this->groupInput(array(
            'id' => '5',
            'name' => 'Valid Group'
        )));

        $this->assertRedirectedTo('/admin/groups');
        $this->assertErrorMessage(
            'editError',
            'The group cannot be edited because it does not exist or may have been deleted.'
        );
        $this->assertSame(2, Group::count());
    }

    /**
     * Test (Fail): name is required
     */
    public function testCharacterizationMissingName()
    {
        $this->assertValidationFailure(
            '/admin/groups/create',
            $this->groupInput(array(
                'name' => '',
                'permission-inherit' => 'admin.view'
            )),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): each permission field uses its own custom regex message
     */
    public function testCharacterizationInvalidPermissionRegexMessages()
    {
        $this->assertValidationFailure(
            '/admin/groups/create',
            $this->groupInput(array(
                'name' => 'Bad permissions',
                'permission-inherit' => 'admin/views',
                'permission-allow' => 'admin view',
                'permission-deny' => 'admin*'
            )),
            array(
                'permission-inherit' => 'The permission inherit format is invalid. Try using the Permission Builder.',
                'permission-allow' => 'The permission allow format is invalid. Try using the Permission Builder.',
                'permission-deny' => 'The permission deny format is invalid. Try using the Permission Builder.'
            )
        );

        $this->assertNull(Group::where('name', 'Bad permissions')->first());
    }

    /**
     * Test (Pass): the regex accepts question marks and stores deny then allow then inherit
     */
    public function testCharacterizationPermissionPatternsAreAccepted()
    {
        $this->postStore($this->groupInput(array(
            'name' => 'Question Group',
            'permission-inherit' => 'admin.view',
            'permission-allow' => 'admin.view,admin.groups.?',
            'permission-deny' => 'admin.users.delete'
        )));

        $this->assertRedirectedTo('/admin/groups');

        $group = Group::where('name', 'Question Group')->first();
        $this->assertNotNull($group);
        $this->assertSame(
            array(
                'admin.users.delete' => -1,
                'admin.view' => 1,
                'admin.groups.?' => 1
            ),
            json_decode($group->permissions, true)
        );
    }

    /**
     * Test (Fail): an array permission value fails the regex rule instead of throwing
     */
    public function testCharacterizationArrayPermissionFailsRegex()
    {
        $this->assertValidationFailure(
            '/admin/groups/create',
            $this->groupInput(array(
                'name' => 'Array permission',
                'permission-allow' => array('admin.view')
            )),
            array(
                'permission-allow' => 'The permission allow format is invalid. Try using the Permission Builder.'
            )
        );

        $this->assertNull(Group::where('name', 'Array permission')->first());
    }

    /**
     * Test (Fail): old input keeps the scalar permission values
     */
    public function testCharacterizationOldInputAfterFailure()
    {
        $this->assertValidationFailure(
            '/admin/groups/create',
            $this->groupInput(array(
                'name' => '',
                'permission-inherit' => 'admin.view',
                'permission-allow' => 'admin.users.view',
                'permission-deny' => 'admin.users.delete'
            )),
            array('name' => 'The name field is required.')
        );

        $this->assertOldInput(array(
            'name' => '',
            'permission-inherit' => 'admin.view',
            'permission-allow' => 'admin.users.view',
            'permission-deny' => 'admin.users.delete'
        ));
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson($this->groupInput(array(
            'name' => '',
            '_error_bag' => 'custom'
        )));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/groups/create');
        $this->assertSessionHasErrors('name');
        $this->assertErrorMessage('name', 'The name field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Fail): an invalid name wins over the nonexistent group lookup
     */
    public function testCharacterizationOrderInvalidNameBeforeNotFound()
    {
        $this->assertValidationFailure(
            '/admin/groups/edit/5',
            $this->groupInput(array('id' => '5', 'name' => '')),
            array('name' => 'The name field is required.')
        );

        $this->assertSame(2, Group::count());
    }

    /**
     * Test (Fail): a valid nonexistent group gives the not-found error before actor grants are checked
     */
    public function testCharacterizationOrderNotFoundBeforeCannotGrant()
    {
        $actor = $this->createUser('group-actor@test.com', array('admin.groups.update' => 1));
        $this->login($actor);

        $this->postStore($this->groupInput(array(
            'id' => '5',
            'name' => 'Valid Group',
            'permission-allow' => 'admin.view'
        )));

        $this->assertRedirectedTo('/admin/groups');
        $this->assertErrorMessage(
            'editError',
            'The group cannot be edited because it does not exist or may have been deleted.'
        );
        $this->assertSame(2, Group::count());
    }

    /**
     * Test (Fail): an invalid permission regex wins over ungrantable permissions
     */
    public function testCharacterizationOrderValidationBeforeCannotGrant()
    {
        $actor = $this->createUser('group-actor@test.com', array('admin.groups.create' => 1));
        $this->login($actor);

        $this->assertValidationFailure(
            '/admin/groups/create',
            $this->groupInput(array(
                'name' => 'Invalid permission',
                'permission-allow' => 'admin view'
            )),
            array(
                'permission-allow' => 'The permission allow format is invalid. Try using the Permission Builder.'
            )
        );

        $this->assertNull(Group::where('name', 'Invalid permission')->first());
    }

    /**
     * Test (Fail): an existing group above the actor is rejected before the submitted permissions
     */
    public function testCharacterizationOrderExistingGroupAboveActor()
    {
        $actor = $this->createUser('group-actor@test.com', array('admin.groups.update' => 1));
        $target = $this->createGroup('Higher Group', array('admin.view' => 1));
        $this->login($actor);

        $this->postStore($this->groupInput(array(
            'id' => $target->id,
            'name' => 'Higher Group'
        )));

        $this->assertRedirectedTo('/admin/groups/edit/' . $target->id);
        $this->assertErrorMessage('group_error_cannot_grant', 'You cannot grant permissions that you do not have.');
        $this->assertSame('Higher Group', $target->fresh()->name);
    }

    /**
     * Test (Fail): the database unique name is not a validation rule, so the save error is shown
     */
    public function testCharacterizationDuplicateNameIsCaughtAtSave()
    {
        $this->postStore($this->groupInput(array('name' => 'Admin')));

        $this->assertRedirectedTo('/admin/groups/create');
        $this->assertErrorMessage(
            'editError',
            'The group cannot be saved due to some problem. Please try again.'
        );
        $this->assertSame(2, Group::count());
    }
}
