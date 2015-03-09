<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Coupon;

class CouponModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Coupon;
        $testcase = array(
            'code' => 'ABC123',
            'description' => 'This is a description',
            'amount' => 10.99,
            'is_percent' => true,
            'start_date' => '02/05/2016 5:39 PM',
            'end_date' => '02/05/2016 5:39 PM',
            'max_spent' => 200.99,
            'min_spent' => 199.88,
            'usage_limit_per_coupon' => 10,
            'usage_limit_per_user' => 1,
            'multiple_coupons' => true,
            'exclude_sale_item' => true,
            'usage_limit_per_coupon_count' => 0
        );
        
        parent::__construct($model, $testcase);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
    }
    
    /**
     * Test (Pass): create a new record without nullable members
     */
    public function testCreateNewPassNoNullable()
    {
        $model = $this->model;
        
        $testcase = array(
            'code' => 'ABC123',
            'amount' => 10.99,
            'is_percent' => true,
            'end_date' => '02/05/2016 5:39 PM',
            'multiple_coupons' => true,
            'exclude_sale_item' => true,
            'usage_limit_per_coupon_count' => 0
        );
        
        foreach ($testcase as $key => $value) {
            $model->$key = $value;
        }

        $result = $model->save();
        
        $this->assertTrue($result == 1); // Saved successfully
        $this->assertTrue($model->id == 1); // 1st record
        
        // Loop through and verify all the properties
        foreach ($testcase as $key => $value) {
            $this->assertTrue($model->$key == $value);
        }
    }
}
