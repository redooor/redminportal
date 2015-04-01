<?php namespace Redooor\Redminportal\Test;

class BaseModelTest extends RedminTestCase
{
    protected $model;
    protected $testcase;
    
    /**
     * Contructor.
     * Must be called explicitly by the child
     *
     * @param object $model A new instance of the model to be tested.
     * @param array $testcase An array of property-value to be used in model creation.
     */
    public function __construct($model, $testcase)
    {
        $this->model = $model;
        $this->testcase = $testcase;
    }
    
    /**
     * Destructor.
     * Must be called explicitly by the child
     */
    public function __destruct()
    {
        $this->model = null;
        $this->testcase = null;
    }
    
    /**
     * Test (Pass): check that $model and $testcase are not null for this test
     */
    public function testPropertiesPass()
    {
        $this->assertTrue($this->model != null);
        $this->assertTrue($this->testcase != null);
    }
    
    /**
     * Test (Pass): get all records
     */
    public function testAllPass()
    {
        $result = $this->model->all();
        $this->assertTrue($result != null);
    }
    
    /**
     * Test (Fail): find record with id = 1
     */
    public function testFind1Fail()
    {
        $result = $this->model->find(1);
        $this->assertTrue($result == null);
    }
    
    /**
     * Test (Pass): get all records by pagination
     */
    public function testPagniatePass()
    {
        $result = $this->model->paginate(20);
        $this->assertTrue($result != null);
    }

    /**
     * Test (Pass): get all records with orderBy
     */
    public function testOrderByPass()
    {
        $result = $this->model->orderBy('updated_at')->get();
        $this->assertTrue($result != null);
    }
    
    /**
     * Test (Pass): create a new record
     */
    public function testCreateNewPass()
    {
        $model = $this->model;
        
        foreach ($this->testcase as $key => $value) {
            $model->$key = $value;
        }

        $result = $model->save();
        
        $this->assertTrue($result == 1); // Saved successfully
        $this->assertTrue($model->id == 1); // 1st record
        
        // Loop through and verify all the properties
        foreach ($this->testcase as $key => $value) {
            $this->assertTrue($model->$key == $value);
        }
    }
    
    /**
     * Test (Pass): find record with id = 1
     */
    public function testFind1Pass()
    {
        $this->testCreateNewPass(); //Create new first

        $model = $this->model->find(1);

        $this->assertTrue($model->id == 1); // 1st record
        
        // Loop through and verify all the properties
        foreach ($this->testcase as $key => $value) {
            $this->assertTrue($model->$key == $value);
        }
    }
    
    /**
     * Test (Pass): find record with id = 1 and delete it
     */
    public function testDestroyPass()
    {
        $this->testCreateNewPass(); //Create new first

        $model = $this->model->find('1');
        $model->delete();

        $result = $this->model->find('1');

        $this->assertTrue($result == null);
    }
}
