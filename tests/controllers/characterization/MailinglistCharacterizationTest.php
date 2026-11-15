<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Mailinglist;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class MailinglistCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/mailinglists';
        $this->viewhas = array(
            'singular' => 'mailinglist',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => $this->mailinglistInput(),
            'edit' => $this->mailinglistInput(array('id' => 1))
        );
    }

    /**
     * A valid mailing-list payload, with optional overrides and omitted keys
     */
    private function mailinglistInput(array $overrides = array(), array $without = array())
    {
        $input = array_merge(array(
            'email' => 'person@example.com',
            'first_name' => 'First',
            'last_name' => 'Last',
            'active' => '1'
        ), $overrides);

        foreach ($without as $key) {
            unset($input[$key]);
        }

        return $input;
    }

    /**
     * Test (Pass): create then update keeps one row and saves the main fields
     */
    public function testCharacterizationCreateThenUpdate()
    {
        $this->postStore($this->mailinglistInput(array(
            'email' => 'original@example.com',
            'first_name' => 'Original first',
            'last_name' => 'Original last',
            'active' => '1'
        )));

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertSame(1, Mailinglist::count());

        $mailinglist = Mailinglist::first();
        $this->assertSame('original@example.com', $mailinglist->email);
        $this->assertSame('Original first', $mailinglist->first_name);
        $this->assertSame('Original last', $mailinglist->last_name);
        $this->assertSame(1, (int) $mailinglist->active);

        $this->postStore($this->mailinglistInput(array(
            'id' => $mailinglist->id,
            'email' => 'updated@example.com',
            'first_name' => 'Updated first',
            'last_name' => 'Updated last',
            'active' => ''
        )));

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertSame(1, Mailinglist::count());

        $mailinglist = Mailinglist::find($mailinglist->id);
        $this->assertSame('updated@example.com', $mailinglist->email);
        $this->assertSame('Updated first', $mailinglist->first_name);
        $this->assertSame('Updated last', $mailinglist->last_name);
        $this->assertSame(0, (int) $mailinglist->active);
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/mailinglists/create',
            $this->mailinglistInput(array(), array('email')),
            array('email' => 'The email field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5 before the not-found check
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/mailinglists/edit/5',
            $this->mailinglistInput(array('id' => '5'), array('email')),
            array('email' => 'The email field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/mailinglists/create',
            $this->mailinglistInput(array('id' => ''), array('email')),
            array('email' => 'The email field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is present and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/mailinglists/edit/0',
            $this->mailinglistInput(array('id' => '0'), array('email')),
            array('email' => 'The email field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $mailinglist = $this->createModel(Mailinglist::class, array(
            'email' => 'existing@example.com',
            'first_name' => 'Existing',
            'last_name' => 'Person',
            'active' => true
        ));

        $this->assertValidationFailure(
            '/admin/mailinglists/edit/' . $mailinglist->id,
            $this->mailinglistInput(array('id' => $mailinglist->id), array('email')),
            array('email' => 'The email field is required.')
        );
    }

    /**
     * Test (Fail): valid input with a nonexistent id gives the storeError not-found message
     */
    public function testCharacterizationNonexistentIdWithValidFields()
    {
        $this->postStore($this->mailinglistInput(array('id' => '5')));

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertErrorMessage(
            'storeError',
            'We are having problem editing this entry. It may have already been deleted.'
        );
        $this->assertSame(0, Mailinglist::count());
    }

    /**
     * Test (Fail): every required field missing gives its exact message
     */
    public function testCharacterizationAllRequiredFieldsMissing()
    {
        $this->assertValidationFailure(
            '/admin/mailinglists/create',
            array(),
            array(
                'email' => 'The email field is required.',
                'first_name' => 'The first name field is required.',
                'last_name' => 'The last name field is required.'
            )
        );
    }

    /**
     * Test (Pass): the email rule has no email format check, so a non-email string is stored
     */
    public function testCharacterizationInvalidEmailFormatIsAccepted()
    {
        $this->postStore($this->mailinglistInput(array('email' => 'not-an-email')));

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertSame(1, Mailinglist::count());
        $this->assertSame('not-an-email', Mailinglist::first()->email);
    }

    /**
     * Test (Fail): creating another row with an existing email fails unique with the exact message
     */
    public function testCharacterizationDuplicateEmailOnCreate()
    {
        $this->createModel(Mailinglist::class, array(
            'email' => 'taken@example.com',
            'first_name' => 'First',
            'last_name' => 'Person',
            'active' => true
        ));

        $this->assertValidationFailure(
            '/admin/mailinglists/create',
            $this->mailinglistInput(array('email' => 'taken@example.com')),
            array('email' => 'The email has already been taken.')
        );

        $this->assertSame(1, Mailinglist::count());
    }

    /**
     * Test (Pass): an unchanged email on update passes unique and another row's email fails
     */
    public function testCharacterizationUniqueEmailOnUpdate()
    {
        $first = $this->createModel(Mailinglist::class, array(
            'email' => 'first@example.com',
            'first_name' => 'First',
            'last_name' => 'Person',
            'active' => true
        ));
        $this->createModel(Mailinglist::class, array(
            'email' => 'second@example.com',
            'first_name' => 'Second',
            'last_name' => 'Person',
            'active' => true
        ));

        $this->postStore($this->mailinglistInput(array(
            'id' => $first->id,
            'email' => 'first@example.com',
            'first_name' => 'Changed first'
        )));

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertSame('Changed first', Mailinglist::find($first->id)->first_name);

        $this->postStore($this->mailinglistInput(array(
            'id' => $first->id,
            'email' => 'second@example.com',
            'first_name' => 'Changed again'
        )));

        $this->assertRedirectedTo('/admin/mailinglists/edit/' . $first->id);
        $this->assertErrorMessage('email', 'The email has already been taken.');
        $this->assertSame('Changed first', Mailinglist::find($first->id)->first_name);
        $this->assertSame(2, Mailinglist::count());
    }

    /**
     * Test (Fail): old input keeps scalar values after a validation failure
     */
    public function testCharacterizationOldInput()
    {
        $this->assertValidationFailure(
            '/admin/mailinglists/create',
            $this->mailinglistInput(array(
                'first_name' => 'Old first',
                'last_name' => 'Old last',
                'active' => '1'
            ), array('email')),
            array('email' => 'The email field is required.')
        );

        $this->assertOldInput(array(
            'first_name' => 'Old first',
            'last_name' => 'Old last',
            'active' => '1'
        ));
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson($this->mailinglistInput(array('_error_bag' => 'custom'), array('email')));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/mailinglists/create');
        $this->assertSessionHasErrors('email');
        $this->assertErrorMessage('email', 'The email field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }
}
