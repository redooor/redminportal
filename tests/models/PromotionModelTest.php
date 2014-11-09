<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Promotion;

class PromotionModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Promotion;
        $testcase = array(
            'name' => 'This is the title',
            'short_description' => 'This is the body',
            'active' => true,
            'start_date' => '2016-02-29 00:00:00',
            'end_date' => '2016-02-29 00:00:00'
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
