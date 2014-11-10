<?php namespace Redooor\Redminportal\Test;

class MailinglistControllerTest extends BaseControllerTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/mailinglists';
        $viewhas = array(
            'singular' => 'mailinglist',
            'plural' => 'mailinglists'
        );
        $input = array(
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
        
        parent::__construct($page, $viewhas, $input);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
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
    
    /**
     * Test (Pass): access getSort by email, asc
     */
    public function testSortByPass()
    {
        $this->client->request('GET', '/admin/mailinglists/sort/email/asc');

        $this->assertResponseOk();
        $this->assertViewHas('mailinglists');
    }
    
    /**
     * Test (Fail): access getSort, try to insert query as email
     */
    public function testSortByValidationFail()
    {
        $this->client->request('GET', '/admin/mailinglists/sort/->where("id", 5)/asc');

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getSort, try to insert query as orderBy
     */
    public function testSortByValidationOrderByFail()
    {
        $this->client->request('GET', '/admin/mailinglists/sort/email/->where("id", 5)');

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertSessionHasErrors();
    }
}
