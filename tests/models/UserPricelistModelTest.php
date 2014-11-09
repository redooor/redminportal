<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\UserPricelist;

class UserPricelistModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new UserPricelist;
        $testcase = array(
            'user_id' => 1,
            'pricelist_id' => 1,
            'paid' => 10.99,
            'transaction_id' => 'UK12345YZ',
            'payment_status' => 'Completed'
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
