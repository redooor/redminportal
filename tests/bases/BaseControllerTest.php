<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;

class BaseControllerTest extends RedminBrowserTestCase
{
    protected $page;
    protected $viewhas;
    protected $input;
    
    /**
     * Initialize Setup with seed
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seed('RedminSeeder');
        
        Auth::guard('redminguard')->loginUsingId(1);
    }
    
    /**
     * Remove saved states
     */
    public function tearDown(): void
    {
        $this->page = null;
        $this->viewhas = null;
        $this->input = null;
    }

    /**
     * A template to create a new record
     */
    protected function createNewModel($model, $testcase)
    {
        foreach ($testcase as $key => $value) {
            $model->$key = $value;
        }

        $model->save();
        
        return $model;
    }
    
    /**
     * A template to check all testcases
     */
    protected function assertTrueModelAllTestcases($model, $testcase)
    {
        foreach ($testcase as $key => $value) {
            $this->assertTrue($model->$key == $value);
        }
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

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
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

        $this->assertRedirectedTo('/');
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
