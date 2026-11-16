<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\User;

/**
 * Covers the email format rule on users.
 */
class UserEmailValidationFixTest extends BaseControllerTest
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
     * Test (Fail): a non-email string is rejected with the default email message
     */
    public function testInvalidEmailIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/users/create',
            $this->userInput(array('email' => 'not-an-email')),
            array('email' => 'The email field must be a valid email address.')
        );

        $this->assertOldInput(array('email' => 'not-an-email'));
        $this->assertSame(1, User::count());
    }

    /**
     * Test (Pass): a valid email is accepted and saved
     */
    public function testValidEmailIsAccepted()
    {
        $this->postStore($this->userInput());

        $this->assertRedirectedTo('/admin/users');
        $this->assertSame(2, User::count());
        $this->assertNotNull(User::where('email', 'valid@example.com')->first());
    }

    /**
     * Test (Pass): an unchanged existing email still passes the email and unique rules on edit
     */
    public function testUnchangedEmailIsAcceptedOnEdit()
    {
        $user = $this->createUser('existing@example.com');

        $this->postStore(array(
            'id' => $user->id,
            'first_name' => 'Changed',
            'last_name' => 'Tester',
            'email' => 'existing@example.com',
            'role' => 1,
            'activated' => '1'
        ));

        $this->assertRedirectedTo('/admin/users');

        $user = User::find($user->id);
        $this->assertSame('existing@example.com', $user->email);
        $this->assertSame('Changed', $user->first_name);
    }

    /**
     * A valid create payload, with optional overrides
     */
    private function userInput(array $overrides = array())
    {
        return array_merge(array(
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'valid@example.com',
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
    private function createUser($email)
    {
        return $this->createModel(User::class, array(
            'email' => $email,
            'password' => Hash::make('test'),
            'first_name' => 'Original',
            'last_name' => 'Tester',
            'activated' => 1,
            'permissions' => json_encode(array())
        ));
    }
}
