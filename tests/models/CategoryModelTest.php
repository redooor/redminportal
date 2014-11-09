<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Category;

class CategoryModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Category;
        $testcase = array(
            'name' => 'This is the title',
            'short_description' => 'This is the body',
            'active' => true
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
