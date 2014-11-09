<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Portfolio;

class PortfolioModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Portfolio;
        $testcase = array(
            'name' => 'This is the title',
            'short_description' => 'This is the body',
            'category_id' => 1
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
