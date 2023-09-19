<?php namespace Redooor\Redminportal\Test;

class MembershipControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/memberships';
        $this->viewhas = array(
            'singular' => 'membership',
            'plural' => 'models'
        );
        $this->input = array(
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
