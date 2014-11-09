<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Discount;

class DiscountModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Discount;
        $testcase = array(
            'code' => 'ABC123',
            'percent' => 10,
            'expiry_date' => '2016-02-29 00:00:00',
            'discountable_id' => 1,
            'discountable_type' => 'Type'
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
}
