<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Purchase;

class PurchaseModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Purchase;
        $testcase = array(
            'user_id' => 1,
            'pricelist_id' => 1
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
