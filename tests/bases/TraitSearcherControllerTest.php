<?php namespace Redooor\Redminportal\Test;

trait TraitSearcherControllerTest
{
    protected $searchable_field;
    protected $search_text;
    
    /**
     * Test (Pass): access postSearch with valid search input
     */
    public function testPostSearchAllPass()
    {
        $input = array(
            'search' => $this->search_text
        );

        $this->call('POST', $this->page . '/search', $input);

        $this->assertRedirectedTo($this->page . '/search-all/' . $this->search_text);
    }
    
    /**
     * Test (Fail): access postSearch with invalid search input
     */
    public function testPostSearchAllFail()
    {
        $input = array(
            'search' => $this->search_text . '$&%*'
        );

        $this->call('POST', $this->page . '/search', $input);

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access postSearch with valid search and field inputs
     */
    public function testPostSearchFieldPassValidField()
    {
        $input = array(
            'field' => $this->searchable_field,
            'search' => $this->search_text
        );

        $this->call('POST', $this->page . '/search', $input);

        $this->assertRedirectedTo($this->page . '/search/' . $this->searchable_field . '/' . $this->search_text);
    }
    
    /**
     * Test (Fail): access postSearch with valid search input but invalid field input
     */
    public function testPostSearchFieldFailInvalidField()
    {
        $input = array(
            'field' => $this->searchable_field . '$&%*',
            'search' => $this->search_text
        );

        $this->call('POST', $this->page . '/search', $input);

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getSearchAll with valid field input but invalid search input
     */
    public function testPostSearchFieldFailInvalidSearch()
    {
        $input = array(
            'field' => $this->searchable_field,
            'search' => $this->search_text . '$&%*'
        );

        $this->call('POST', $this->page . '/search', $input);

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getSearchAll with valid input
     */
    public function testGetSearchAllPass()
    {
        $this->call('GET', $this->page . '/search-all/' . $this->search_text);

        $this->assertResponseOk();
        $this->assertViewHas('models');
    }
    
    /**
     * Test (Fail): access getSearchAll with invalid input
     */
    public function testGetSearchAllFail()
    {
        $this->call('GET', $this->page . '/search-all/' . $this->search_text . '$&%*');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getSearch with valid input
     */
    public function testGetSearchWithFieldPass()
    {
        $this->call('GET', $this->page . '/search/' . $this->searchable_field . '/' . $this->search_text);

        $this->assertResponseOk();
        $this->assertViewHas('models');
    }
    
    /**
     * Test (Fail): access getSearch with invalid field input
     */
    public function testGetSearchWithFieldFailInvalidField()
    {
        $this->call('GET', $this->page . '/search/' . $this->searchable_field . '$&%*/' . $this->search_text);

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getSearch with invalid search input
     */
    public function testGetSearchWithFieldFailInvalidSearch()
    {
        $this->call('GET', $this->page . '/search/' . $this->searchable_field . '/$&%*' . $this->search_text);

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
}
