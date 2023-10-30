<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\UserPricelist;

class UserPricelistModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new UserPricelist;
        $testcase = array(
            'user_id' => 1,
            'pricelist_id' => 1,
            'paid' => 10.99,
            'transaction_id' => 'UK12345YZ',
            'payment_status' => 'Completed'
        );
        
        $this->prepare($model, $testcase);
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
        $testcase = array(
            'user_id' => 1,
            'paid' => 10.99,
            'transaction_id' => 'UK12345YZ',
            'payment_status' => 'Completed'
        );
        foreach ($testcase as $key => $value) {
            $this->assertTrue($model->$key == $value);
        }
    }
    
    /**
     * Test (Pass): override find record with id = 1
     */
    public function testFind1Pass()
    {
        // Create Pricelist
        $pricelist = new Pricelist;
        $pricelist->price = 10;
        $pricelist->module_id = 1;
        $pricelist->membership_id = 1;
        $pricelist->active = true;
        $pricelist->save();
        
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
