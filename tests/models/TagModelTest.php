<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Tag;

class TagModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Tag;
        $testcase = array(
            'name' => 'This is a tag'
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
