<?php namespace Redooor\Redminportal\Test;

class BaseControllerTest extends RedminTestCase
{
    protected $page;
    protected $viewhas;
    protected $input;
    
    /**
     * Contructor.
     * Must be called explicitly by the child
     *
     * @param string $page The name of the page to be tested.
     * @param string $view The name of the page to be tested.
     */
    public function __construct($page, $viewhas, $input)
    {
        $this->page = $page;
        $this->viewhas = $viewhas;
        $this->input = $input;
    }
    
    /**
     * Destructor.
     * Must be called explicitly by the child
     */
    public function __destruct()
    {
        $this->page = null;
    }
    
    /**
     * Test (Pass): access getIndex
     */
    public function testIndex()
    {
        $this->call('GET', $this->page);

        $this->assertResponseOk();
        $this->assertViewHas($this->viewhas['plural']);
    }
    
    /**
     * Test (Pass): access getCreate
     */
    public function testCreatePass()
    {
        $this->call('GET', $this->page . '/create');

        $this->assertResponseOk();
    }
    
    /**
     * Test (Pass): access postStore with input
     */
    public function testStoreCreatePass()
    {
        $this->call('POST', $this->page . '/store', $this->input['create']);

        $this->assertRedirectedTo($this->page);
    }
    
    /**
     * Test (Fail): access getEdit, return 404
     */
    public function testEditFail404()
    {
        $this->call('GET', $this->page . '/edit/1');

        $this->assertResponseStatus(302); // Redirected
    }
    
    /**
     * Test (Pass): access getEdit
     */
    public function testEditPass()
    {
        $this->testStoreCreatePass();

        $this->call('GET', $this->page . '/edit/1');

        $this->assertResponseOk();
        $this->assertViewHas($this->viewhas['singular']);
    }
    
    /**
     * Test (Pass): access postStore with id and input
     */
    public function testStoreEdit()
    {
        $this->call('POST', $this->page . '/store', $this->input['edit']);

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors(); // Fail because there's no record
        
        $this->testStoreCreatePass(); // Now add a new record
        
        $this->call('POST', $this->page . '/store', $this->input['edit']);

        $this->assertRedirectedTo($this->page);
    }
    
    /**
     * Test (Fail): access getDelete with id = 1
     */
    public function testDeleteFail()
    {
        $this->call('GET', $this->page . '/delete/1');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getDelete with id = 1
     */
    public function testDeletePass()
    {
        $this->testStoreCreatePass();
        
        $this->call('GET', $this->page . '/delete/1');

        $this->assertResponseStatus(302); // Redirected
    }
}
