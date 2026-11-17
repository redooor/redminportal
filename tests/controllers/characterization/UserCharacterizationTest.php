<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class UserCharacterizationTest extends BaseControllerTest
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
            'create' => array(
                'first_name' => 'Peter',
                'last_name' => 'Lim',
                'email' => 'peter.lim@test.com',
                'password' => 'Passw0rd-1234',
                'password_confirmation' => 'Passw0rd-1234',
                'role' => 1,
                'activated' => true,
                'permission-inherit' => 'admin.view',
                'permission-allow' => 'admin.create,admin.update',
                'permission-deny' => 'admin.delete'
            ),
            'edit' => array(
                'id' => 2,
                'first_name' => 'Peter',
                'last_name' => 'Lim',
                'email' => 'peter.lim@test.com',
                'password' => 'Passw0rd-1234',
                'password_confirmation' => 'Passw0rd-1234',
                'role' => 1,
                'activated' => true,
                'permission-inherit' => 'admin.view',
                'permission-allow' => 'admin.create,admin.update',
                'permission-deny' => 'admin.delete'
            )
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

    private function createUser($email, array $permissions = array())
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

    private function userInput($role, array $overrides = array())
    {
        return array_merge(array(
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'created@test.com',
            'password' => 'Passw0rd-1234',
            'password_confirmation' => 'Passw0rd-1234',
            'role' => $role,
            'activated' => 1,
            'permission-inherit' => '',
            'permission-allow' => '',
            'permission-deny' => ''
        ), $overrides);
    }

    /**
     * Test (Pass): create then update keeps the row count and the existing password hash
     */
    public function testCharacterizationCreateThenUpdate()
    {
        $this->postStore($this->userInput(1));

        $this->assertRedirectedTo('/admin/users');
        $this->assertSame(2, User::count());

        $user = User::where('email', 'created@test.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('New', $user->first_name);
        $this->assertSame('User', $user->last_name);
        $this->assertSame(1, (int) $user->activated);
        $this->assertTrue(Hash::check('Passw0rd-1234', $user->password));

        $hash = $user->password;

        $this->postStore(array(
            'id' => $user->id,
            'first_name' => 'Updated',
            'last_name' => 'User',
            'email' => 'created@test.com',
            'role' => 1,
            'activated' => '1'
        ));

        $this->assertRedirectedTo('/admin/users');
        $this->assertSame(2, User::count());

        $user = User::find($user->id);
        $this->assertSame('Updated', $user->first_name);
        $this->assertSame($hash, $user->password);
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(1, array('first_name' => '')),
            array('first_name' => 'The first name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/users/edit/5',
            $this->userInput(1, array('id' => '5', 'first_name' => '')),
            array('first_name' => 'The first name field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(1, array('id' => '', 'first_name' => '')),
            array('first_name' => 'The first name field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is present and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/users/edit/0',
            $this->userInput(1, array('id' => '0', 'first_name' => '')),
            array('first_name' => 'The first name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $target = $this->createUser('target@test.com');

        $this->assertValidationFailure(
            '/admin/users/edit/' . $target->id,
            $this->userInput(1, array(
                'id' => $target->id,
                'first_name' => '',
                'email' => $target->email
            )),
            array('first_name' => 'The first name field is required.')
        );
    }

    /**
     * Test (Fail): valid input with a nonexistent id gives the createError not-found message
     */
    public function testCharacterizationNonexistentIdWithValidFields()
    {
        $input = $this->userInput(1, array(
            'id' => '5',
            'email' => 'valid@test.com'
        ));
        unset($input['password'], $input['password_confirmation']);

        $this->postStore($input);

        $this->assertRedirectedTo('/admin/users');
        $this->assertErrorMessage(
            'createError',
            'The user cannot be found or created. Please try again later.'
        );
        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): all required fields on create use the default messages
     */
    public function testCharacterizationAllRequiredFieldsMissing()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            array(),
            array(
                'first_name' => 'The first name field is required.',
                'last_name' => 'The last name field is required.',
                'role' => 'The role field is required.',
                'email' => 'The email field is required.',
                'password' => 'The password field is required.'
            )
        );

        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): an email address used by another row fails the unique rule
     */
    public function testCharacterizationDuplicateEmailOnCreate()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(1, array('email' => 'admin@admin.com')),
            array('email' => 'The email has already been taken.')
        );

        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): the email rule rejects a malformed email with the default message
     */
    public function testCharacterizationMalformedEmailIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(1, array('email' => 'not-an-email')),
            array('email' => 'The email field must be a valid email address.')
        );

        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): each permission field uses its own custom regex message
     */
    public function testCharacterizationInvalidPermissionRegexMessages()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(1, array(
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

        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): an array permission value fails the regex rule instead of throwing
     */
    public function testCharacterizationArrayPermissionFailsRegex()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(1, array('permission-allow' => array('admin.view'))),
            array(
                'permission-allow' => 'The permission allow format is invalid. Try using the Permission Builder.'
            )
        );

        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): old input keeps the scalar values, including the password
     */
    public function testCharacterizationOldInputAfterFailure()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(1, array(
                'first_name' => '',
                'email' => 'old@test.com',
                'permission-allow' => 'admin.view',
                'activated' => '1'
            )),
            array('first_name' => 'The first name field is required.')
        );

        $this->assertOldInput(array(
            'first_name' => '',
            'last_name' => 'User',
            'email' => 'old@test.com',
            'password' => 'Passw0rd-1234',
            'role' => 1,
            'activated' => '1',
            'permission-allow' => 'admin.view'
        ));
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson($this->userInput(1, array(
            'first_name' => '',
            '_error_bag' => 'custom'
        )));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/users/create');
        $this->assertSessionHasErrors('first_name');
        $this->assertErrorMessage('first_name', 'The first name field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Fail): self-deactivation is checked before validation
     */
    public function testCharacterizationOrderSelfDeactivationBeforeValidation()
    {
        $this->postStore(array(
            'id' => '1',
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'role' => '',
            'activated' => ''
        ));

        $this->assertRedirectedTo('/admin/users/edit/1');
        $this->assertErrorMessage(
            'deactivateError',
            'You cannot deactivate your own account while you are logged in. Try using another account.'
        );
        $this->assertFalse($this->defaultErrors()->has('first_name'));
        $this->assertSame(1, (int) User::find(1)->activated);
    }

    /**
     * Test (Fail): invalid fields win over the nonexistent user lookup on edit/5
     */
    public function testCharacterizationOrderValidationBeforeNotFound()
    {
        $this->postStore($this->userInput(1, array(
            'id' => '5',
            'first_name' => '',
            'email' => 'valid@test.com',
            'activated' => '1'
        )));

        $this->assertRedirectedTo('/admin/users/edit/5');
        $this->assertErrorMessage('first_name', 'The first name field is required.');
        $this->assertFalse($this->defaultErrors()->has('createError'));
        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): a protected account with an invalid permission regex reports the regex first
     */
    public function testCharacterizationOrderValidationBeforeProtectedAccount()
    {
        $actor = $this->createUser('actor@test.com', array('admin.users.update' => 1));
        $target = $this->createUser('target@test.com', array('admin.view' => 1));
        $this->login($actor);

        $this->postStore($this->userInput(1, array(
            'id' => $target->id,
            'first_name' => 'Changed',
            'email' => $target->email,
            'permission-allow' => 'admin view',
            'activated' => '1'
        )));

        $this->assertRedirectedTo('/admin/users/edit/' . $target->id);
        $this->assertErrorMessage(
            'permission-allow',
            'The permission allow format is invalid. Try using the Permission Builder.'
        );
        $this->assertFalse($this->defaultErrors()->has('user_error_protected_account'));
        $this->assertSame('Original', $target->fresh()->first_name);
    }

    /**
     * Test (Fail): a protected account wins over ungrantable submitted permissions
     */
    public function testCharacterizationOrderProtectedAccountBeforeCannotGrant()
    {
        $actor = $this->createUser('actor@test.com', array('admin.users.update' => 1));
        $target = $this->createUser('target@test.com', array('admin.view' => 1));
        $this->login($actor);

        $this->postStore($this->userInput(1, array(
            'id' => $target->id,
            'first_name' => 'Changed',
            'email' => $target->email,
            'permission-allow' => 'admin.pages.view',
            'activated' => '1'
        )));

        $this->assertRedirectedTo('/admin/users/edit/' . $target->id);
        $this->assertErrorMessage(
            'user_error_protected_account',
            'You cannot change an account that has more access than you.'
        );
        $this->assertFalse($this->defaultErrors()->has('user_error_cannot_grant'));
        $this->assertSame('Original', $target->fresh()->first_name);
    }

    /**
     * Test (Fail): ungrantable submitted permissions win over an ungrantable submitted group
     */
    public function testCharacterizationOrderPermissionsBeforeSubmittedGroup()
    {
        $actor = $this->createUser('actor@test.com', array('admin.users.create' => 1));
        $highGroup = $this->createGroup('High Group', array('admin.view' => 1));
        $this->login($actor);

        $this->postStore($this->userInput($highGroup->id, array(
            'email' => 'blocked@test.com',
            'permission-allow' => 'admin.pages.view'
        )));

        $this->assertRedirectedTo('/admin/users/create');
        $this->assertErrorMessage(
            'user_error_cannot_grant',
            'You cannot grant permissions that you do not have.'
        );
        $this->assertFalse($this->defaultErrors()->has('groupError'));
        $this->assertNull(User::where('email', 'blocked@test.com')->first());
    }

    /**
     * Test (Fail): the permission check also runs before a missing submitted group can fail at addGroup
     */
    public function testCharacterizationOrderPermissionsBeforeMissingGroup()
    {
        $actor = $this->createUser('actor@test.com', array('admin.users.create' => 1));
        $this->login($actor);

        $this->postStore($this->userInput(999, array(
            'email' => 'blocked@test.com',
            'permission-allow' => 'admin.pages.view'
        )));

        $this->assertRedirectedTo('/admin/users/create');
        $this->assertErrorMessage(
            'user_error_cannot_grant',
            'You cannot grant permissions that you do not have.'
        );
        $this->assertFalse($this->defaultErrors()->has('groupError'));
        $this->assertNull(User::where('email', 'blocked@test.com')->first());
    }

    /**
     * Test (Fail): an empty password on create only fails the required rule
     */
    public function testCharacterizationCreateEmptyPasswordFailsRequired()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(1, array(
                'password' => '',
                'password_confirmation' => ''
            )),
            array('password' => 'The password field is required.')
        );

        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): a short password on create uses the configured minimum length
     */
    public function testCharacterizationCreateShortPasswordFailsMin()
    {
        Config::set('redminportal::password.min_length', 10);

        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(1, array(
                'password' => 'Passw0rd1',
                'password_confirmation' => 'Passw0rd1'
            )),
            array('password' => 'The password field must be at least 10 characters.')
        );

        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): a password confirmation mismatch uses the default confirmed message
     */
    public function testCharacterizationCreatePasswordConfirmationMismatch()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(1, array(
                'password' => 'Passw0rd-1234',
                'password_confirmation' => 'Different-1234'
            )),
            array('password' => 'The password field confirmation does not match.')
        );

        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): an explicit empty password on edit is not skipped, it fails min
     */
    public function testCharacterizationEditEmptyStringPasswordFailsMin()
    {
        Config::set('redminportal::password.min_length', 10);

        $target = $this->createUser('target@test.com');
        $hash = $target->password;

        $this->postStore(array(
            'id' => $target->id,
            'first_name' => 'Target',
            'last_name' => 'Tester',
            'email' => $target->email,
            'password' => '',
            'password_confirmation' => '',
            'role' => 1,
            'activated' => '1'
        ));

        $this->assertRedirectedTo('/admin/users/edit/' . $target->id);
        $this->assertErrorMessage('password', 'The password field must be at least 10 characters.');
        $this->assertSame($hash, $target->fresh()->password);
    }

    /**
     * Test (Pass): an edit that omits the password keeps the old hash
     */
    public function testCharacterizationEditWithoutPasswordKeepsHash()
    {
        $target = $this->createUser('target@test.com');
        $hash = $target->password;

        $this->postStore(array(
            'id' => $target->id,
            'first_name' => 'Changed',
            'last_name' => 'Tester',
            'email' => $target->email,
            'role' => 1,
            'activated' => '1'
        ));

        $this->assertRedirectedTo('/admin/users');
        $this->assertSame('Changed', $target->fresh()->first_name);
        $this->assertSame($hash, $target->fresh()->password);
    }

    /**
     * Test (Fail): a short non-empty password on edit fails min and keeps the old hash
     */
    public function testCharacterizationEditShortPasswordFailsMin()
    {
        Config::set('redminportal::password.min_length', 10);

        $target = $this->createUser('target@test.com');
        $hash = $target->password;

        $this->postStore(array(
            'id' => $target->id,
            'first_name' => 'Target',
            'last_name' => 'Tester',
            'email' => $target->email,
            'password' => 'Passw0rd1',
            'password_confirmation' => 'Passw0rd1',
            'role' => 1,
            'activated' => '1'
        ));

        $this->assertRedirectedTo('/admin/users/edit/' . $target->id);
        $this->assertErrorMessage('password', 'The password field must be at least 10 characters.');
        $this->assertSame($hash, $target->fresh()->password);
    }

    /**
     * Test (Pass): a role array assigns every submitted group
     */
    public function testCharacterizationRoleArrayAssignsMultipleGroups()
    {
        $this->postStore($this->userInput(array(1, 2)));

        $this->assertRedirectedTo('/admin/users');

        $user = User::where('email', 'created@test.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(
            array(1, 2),
            $user->groups()->orderBy('groups.id')->pluck('groups.id')->all()
        );
    }

    /**
     * Test (Fail): an empty string role is required
     */
    public function testCharacterizationRoleEmptyStringIsRequired()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(''),
            array('role' => 'The role field is required.')
        );

        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): an empty array role is required
     */
    public function testCharacterizationRoleEmptyArrayIsRequired()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(array()),
            array('role' => 'The role field is required.')
        );

        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): an unknown role id is rejected before the user row is saved
     */
    public function testCharacterizationUnknownRoleLeavesNoUserRow()
    {
        $this->postStore($this->userInput(999, array('email' => 'unknown-role@test.com')));

        $this->assertRedirectedTo('/admin/users/create');
        $this->assertErrorMessage(
            'groupError',
            'The user cannot be updated because the selected group cannot be found. Please try again.'
        );

        $this->assertNull(User::where('email', 'unknown-role@test.com')->first());
        $this->assertSame(1, User::count());
    }

    /**
     * Test (Fail): the unique rule ignores the row being updated but rejects another row's email
     */
    public function testCharacterizationDuplicateEmailOnUpdate()
    {
        $target = $this->createUser('target@test.com');

        $this->postStore(array(
            'id' => $target->id,
            'first_name' => 'Target',
            'last_name' => 'Tester',
            'email' => 'admin@admin.com',
            'role' => 1,
            'activated' => '1'
        ));

        $this->assertRedirectedTo('/admin/users/edit/' . $target->id);
        $this->assertErrorMessage('email', 'The email has already been taken.');
        $this->assertSame('target@test.com', $target->fresh()->email);
    }

    /**
     * Test (Pass): the activated checkbox maps '0' to active and an empty or missing value to inactive
     */
    public function testCharacterizationActivatedCheckboxMapping()
    {
        $cases = array(
            'activated-one@test.com' => array('1', 1),
            'activated-zero@test.com' => array('0', 1),
            'activated-empty@test.com' => array('', 0),
            'activated-missing@test.com' => array(null, 0)
        );

        foreach ($cases as $email => $case) {
            $input = $this->userInput(1, array('email' => $email));

            if ($case[0] === null) {
                unset($input['activated']);
            } else {
                $input['activated'] = $case[0];
            }

            $this->postStore($input);
            $this->assertRedirectedTo('/admin/users');

            $user = User::where('email', $email)->first();
            $this->assertNotNull($user);
            $this->assertSame($case[1], (int) $user->activated, $email);
        }

        $this->assertSame(5, User::count());
    }

    /**
     * Test (Pass): an empty activated value on an edit deactivates another user
     */
    public function testCharacterizationEditActivatedEmptyDeactivates()
    {
        $target = $this->createUser('target@test.com');

        $this->postStore(array(
            'id' => $target->id,
            'first_name' => 'Target',
            'last_name' => 'Tester',
            'email' => $target->email,
            'role' => 1,
            'activated' => ''
        ));

        $this->assertRedirectedTo('/admin/users');
        $this->assertSame(0, (int) $target->fresh()->activated);
    }
}
