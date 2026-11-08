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
     * Test (Pass): an empty list says nothing was found and offers no "Previous Page" button that points back to itself.
     */
    public function testEmptyListShowsNotFoundMessageWithoutPreviousPage()
    {
        $this->call('GET', '/admin/memberships');

        $this->assertResponseOk();

        $html = $this->response->getContent();

        $this->assertStringContainsString('No memberships found.', $html);
        $this->assertStringNotContainsString('Previous Page', $html);
        $this->assertStringNotContainsString('alert-info', $html);
    }

    /**
     * Test (Pass): a page past the last one offers a "Previous Page" button to the last page with data.
     */
    public function testPageBeyondLastShowsPreviousPageButton()
    {
        $membership = new \Redooor\Redminportal\App\Models\Membership;
        $membership->name = 'Only one';
        $membership->rank = 1;
        $membership->save();

        $this->call('GET', '/admin/memberships?page=9');

        $this->assertResponseOk();

        $html = $this->response->getContent();

        $this->assertStringContainsString('This page is empty', $html);
        $this->assertStringContainsString('Previous Page', $html);
        $this->assertStringContainsString('page=1', $html);
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
