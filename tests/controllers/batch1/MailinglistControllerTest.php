<?php namespace Redooor\Redminportal\Test;

class MailinglistControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/mailinglists';
        $this->viewhas = array(
            'singular' => 'mailinglist',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'email' => 'peter.lim@test.com',
                'first_name' => 'Peter',
                'last_name' => 'Lim'
            ),
            'edit' => array(
                'id'        => 1,
                'email' => 'peter2.lim@test.com',
                'first_name' => 'Peter2',
                'last_name' => 'Lim2'
            )
        );
        
        // For testing sort
        $this->sortBy = 'email';
    }
    
    /**
     * Test (Fail): access postStore with no email
     */
    public function testStoreCreateFailsEmailBlank()
    {
        $input = array(
            'email'                 => '',
            'first_name'            => 'Peter',
            'last_name'             => 'Lim'
        );

        $this->call('POST', '/admin/mailinglists/store', $input);

        $this->assertRedirectedTo('/admin/mailinglists/create');
        $this->assertSessionHasErrors();
    }
}
