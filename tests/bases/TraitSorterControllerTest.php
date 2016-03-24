<?php namespace Redooor\Redminportal\Test;

trait TraitSorterControllerTest
{
    /* Requires
    protected $page
    */
    protected $sortBy;
    
    /**
     * Test (Pass): access getSort by email, asc
     */
    public function testSortByPass()
    {
        $this->call('GET', $this->page . '/sort/' . $this->sortBy . '/asc');

        $this->assertResponseOk();
        $this->assertViewHas('models');
    }
    
    /**
     * Test (Fail): access getSort, try to insert query as email
     */
    public function testSortByValidationFail()
    {
        $this->call('GET', $this->page . '/sort/' . '->where("id", 5)/asc');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getSort, try to insert query as orderBy
     */
    public function testSortByValidationOrderByFail()
    {
        $this->call('GET', $this->page . '/sort/' . $this->sortBy . '/->where("id", 5)');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
}
