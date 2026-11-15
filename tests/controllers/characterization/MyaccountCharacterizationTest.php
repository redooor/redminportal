<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\User;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class MyaccountCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = 'myaccount';
        $this->viewhas = array(
            'singular' => 'user',
            'plural' => 'user'
        );
        $this->user = User::find(1);
        $this->input = array(
            'create' => $this->validInput(),
            'edit' => $this->validInput(array('id' => '1'))
        );
    }

    private function validInput(array $overrides = array())
    {
        return array_merge(array(
            'first_name' => 'Peter',
            'last_name' => 'Lim',
            'old_password' => 'admin'
        ), $overrides);
    }

    /**
     * Overwrite base function, Myaccount has no create page
     */
    public function testCreatePass()
    {
        $this->call('GET', 'myaccount/create');

        $this->assertResponseStatus(404);
    }

    /**
     * Overwrite base function, Myaccount has no edit page
     */
    public function testEditFail404()
    {
        $this->call('GET', 'myaccount/edit/1');

        $this->assertResponseStatus(404);
    }

    /**
     * Overwrite base function, Myaccount has no edit page
     */
    public function testEditPass()
    {
        $this->call('GET', 'myaccount/edit/1');

        $this->assertResponseStatus(404);
    }

    /**
     * Overwrite base function, a posted id is ignored and the account is still saved
     */
    public function testStoreEdit()
    {
        $this->postStore($this->validInput(array('id' => '1')));

        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHas(
            'success_message',
            'Your changes have been saved.'
        );
    }

    /**
     * Overwrite base function, Myaccount has no delete route
     */
    public function testDeleteFail()
    {
        $this->call('POST', 'myaccount/delete/1');

        $this->assertResponseStatus(404);
    }

    /**
     * Overwrite base function, Myaccount has no delete route
     */
    public function testDeletePass()
    {
        $this->call('POST', 'myaccount/delete/1');

        $this->assertResponseStatus(404);
    }

    /**
     * Test (Fail): a guest is redirected to login before any validation runs
     */
    public function testCharacterizationGuestRedirectedToLoginBeforeValidation()
    {
        Auth::guard('redminguard')->logout();

        $this->postStore(array(
            'first_name' => '',
            'last_name' => '',
            'old_password' => ''
        ));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('login');
        $this->assertNull(session('errors'));
    }

    /**
     * Test (Fail): first_name is required
     */
    public function testCharacterizationMissingFirstName()
    {
        $this->assertValidationFailure(
            'myaccount',
            $this->validInput(array('first_name' => '')),
            array('first_name' => 'The first name field is required.')
        );
    }

    /**
     * Test (Fail): last_name is required
     */
    public function testCharacterizationMissingLastName()
    {
        $this->assertValidationFailure(
            'myaccount',
            $this->validInput(array('last_name' => '')),
            array('last_name' => 'The last name field is required.')
        );
    }

    /**
     * Test (Fail): old_password is required with the controller's custom message
     */
    public function testCharacterizationMissingOldPassword()
    {
        $this->assertValidationFailure(
            'myaccount',
            array('first_name' => 'Peter', 'last_name' => 'Lim'),
            array('old_password' => 'You need to provide your existing password to save any changes.')
        );
    }

    /**
     * Test (Fail): a mismatched password confirmation gives the exact message
     */
    public function testCharacterizationPasswordConfirmationMismatch()
    {
        $this->assertValidationFailure(
            'myaccount',
            $this->validInput(array(
                'password' => 'NewPassw0rd123',
                'password_confirmation' => 'NewPassw0rd124'
            )),
            array('password' => 'The password field confirmation does not match.')
        );
    }

    /**
     * Test (Fail): a password present without a confirmation gives the exact message
     */
    public function testCharacterizationPasswordConfirmationMissing()
    {
        $this->assertValidationFailure(
            'myaccount',
            $this->validInput(array('password' => 'NewPassw0rd123')),
            array('password' => 'The password field confirmation does not match.')
        );
    }

    /**
     * Test (Fail): a password shorter than the configured minimum gives the exact message
     */
    public function testCharacterizationPasswordTooShort()
    {
        Config::set('redminportal::password.min_length', 10);

        $this->assertValidationFailure(
            'myaccount',
            $this->validInput(array(
                'password' => 'Passw0rd1',
                'password_confirmation' => 'Passw0rd1'
            )),
            array('password' => 'The password field must be at least 10 characters.')
        );
    }

    /**
     * Test (Fail): validation failures win over a wrong old password
     */
    public function testCharacterizationValidationBeatsWrongOldPassword()
    {
        $this->postStore($this->validInput(array(
            'first_name' => '',
            'old_password' => 'wrong-password'
        )));

        $this->assertRedirectedTo('myaccount');
        $this->assertErrorMessage('first_name', 'The first name field is required.');
        $this->assertSame('', $this->defaultErrors()->first('saveError'));
    }

    /**
     * Test (Fail): old input keeps the scalar values after a validation failure
     */
    public function testCharacterizationOldInputAfterValidationFailure()
    {
        $this->assertValidationFailure(
            'myaccount',
            $this->validInput(array('last_name' => '')),
            array('last_name' => 'The last name field is required.')
        );

        $this->assertOldInput(array(
            'first_name' => 'Peter',
            'old_password' => 'admin'
        ));
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson($this->validInput(array(
            'first_name' => '',
            '_error_bag' => 'custom'
        )));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHasErrors('first_name');
        $this->assertErrorMessage('first_name', 'The first name field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Fail): a wrong old password gives saveError with the authentication message
     */
    public function testCharacterizationWrongOldPassword()
    {
        $oldHash = $this->user->password;

        $this->postStore($this->validInput(array('old_password' => 'wrong-password')));

        $this->assertRedirectedTo('myaccount');
        $this->assertErrorMessage(
            'saveError',
            'We had a problem authenticating your access. Please try again.'
        );
        $this->assertOldInput(array(
            'first_name' => 'Peter',
            'old_password' => 'wrong-password'
        ));

        $this->user = User::find(1);
        $this->assertSame('System', $this->user->first_name);
        $this->assertSame('Admin', $this->user->last_name);
        $this->assertSame($oldHash, $this->user->password);
    }

    /**
     * Test (Pass): a valid save without a new password keeps the hash and updates the names
     */
    public function testCharacterizationSuccessWithoutPasswordKeepsHash()
    {
        $oldHash = $this->user->password;

        $this->postStore($this->validInput());

        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHas(
            'success_message',
            'Your changes have been saved.'
        );

        $this->user = User::find(1);
        $this->assertSame('Peter', $this->user->first_name);
        $this->assertSame('Lim', $this->user->last_name);
        $this->assertSame($oldHash, $this->user->password);
        $this->assertTrue(Hash::check('admin', $this->user->password));

        $this->assertSame(1, (int) session('redmin_fingerprint_user'));
        $this->assertSame(hash('sha256', $oldHash), session('redmin_password_fingerprint'));
    }

    /**
     * Test (Pass): a valid save with a new password updates the hash and session fingerprints
     */
    public function testCharacterizationSuccessWithPasswordUpdatesFingerprints()
    {
        $oldHash = $this->user->password;

        $this->postStore($this->validInput(array(
            'password' => 'NewPassw0rd123',
            'password_confirmation' => 'NewPassw0rd123'
        )));

        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHas(
            'success_message',
            'Your changes have been saved.'
        );

        $this->user = User::find(1);
        $this->assertSame('Peter', $this->user->first_name);
        $this->assertSame('Lim', $this->user->last_name);
        $this->assertNotSame($oldHash, $this->user->password);
        $this->assertTrue(Hash::check('NewPassw0rd123', $this->user->password));
        $this->assertFalse(Hash::check('admin', $this->user->password));

        $this->assertSame(1, (int) session('redmin_fingerprint_user'));
        $this->assertSame(
            hash('sha256', $this->user->password),
            session('redmin_password_fingerprint')
        );
        $this->assertNotSame(hash('sha256', $oldHash), session('redmin_password_fingerprint'));
    }
}
