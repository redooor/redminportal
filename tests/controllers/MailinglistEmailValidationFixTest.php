<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Mailinglist;

/**
 * Covers the email format rule on mailinglists.
 */
class MailinglistEmailValidationFixTest extends BaseControllerTest
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
            'edit' => $this->mailinglistInput(array(
                'id' => 1,
                'email' => 'edited@example.com'
            ))
        );
    }

    /**
     * Test (Fail): a non-email string is rejected with the default email message
     */
    public function testInvalidEmailIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/mailinglists/create',
            $this->mailinglistInput(array('email' => 'not-an-email')),
            array('email' => 'The email field must be a valid email address.')
        );

        $this->assertOldInput(array('email' => 'not-an-email'));
        $this->assertSame(0, Mailinglist::count());
    }

    /**
     * Test (Pass): a valid email is accepted and saved
     */
    public function testValidEmailIsAccepted()
    {
        $this->postStore($this->mailinglistInput());

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertSame(1, Mailinglist::count());

        $mailinglist = Mailinglist::first();
        $this->assertSame('valid@example.com', $mailinglist->email);
        $this->assertSame('First', $mailinglist->first_name);
        $this->assertSame('Last', $mailinglist->last_name);
    }

    /**
     * Test (Pass): an unchanged existing email still passes the email and unique rules on edit
     */
    public function testUnchangedEmailIsAcceptedOnEdit()
    {
        $mailinglist = $this->createModel(Mailinglist::class, array(
            'email' => 'existing@example.com',
            'first_name' => 'Existing',
            'last_name' => 'Person',
            'active' => true
        ));

        $this->postStore($this->mailinglistInput(array(
            'id' => $mailinglist->id,
            'email' => 'existing@example.com',
            'first_name' => 'Changed'
        )));

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertSame(1, Mailinglist::count());

        $mailinglist = Mailinglist::find($mailinglist->id);
        $this->assertSame('existing@example.com', $mailinglist->email);
        $this->assertSame('Changed', $mailinglist->first_name);
    }

    /**
     * A valid mailing-list payload, with optional overrides
     */
    private function mailinglistInput(array $overrides = array())
    {
        return array_merge(array(
            'email' => 'valid@example.com',
            'first_name' => 'First',
            'last_name' => 'Last',
            'active' => '1'
        ), $overrides);
    }
}
