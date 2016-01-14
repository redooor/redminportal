<?php namespace Redooor\Redminportal\Test;

class MembershipControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest;
    
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/memberships';
        $viewhas = array(
            'singular' => 'membership',
            'plural' => 'models'
        );
        $input = array(
            'create' => array(
                'name' => 'This is title',
                'rank' => 1
            ),
            'edit' => array(
                'id'   => 1,
                'name' => 'This is title',
                'rank' => 1
            )
        );
        
        // For testing sort
        $this->sortBy = 'rank';
        
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
     * Test (Fail): access postStore with input but no name
     */
    public function testStoreCreateFailsNameBlank()
    {
        $input = array(
            'name'     => '',
            'rank'     => 1
        );

        $this->call('POST', '/admin/memberships/store', $input);

        $this->assertRedirectedTo('/admin/memberships/create');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access postStore with input but no rank
     */
    public function testStoreEditFails()
    {
        $this->testStoreCreatePass();

        $input = array(
            'id'      => 1,
            'name'    => 'This is title'
        );

        $this->call('POST', '/admin/memberships/store', $input);

        $this->assertRedirectedTo('/admin/memberships/edit/1');
        $this->assertSessionHasErrors();
    }
}
