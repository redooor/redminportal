<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Group;

class GroupModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Group;
        $testcase = array(
            'name'  => 'test',
            'permissions' => "{'admin.view':'1','admin.create':'0'}"
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
