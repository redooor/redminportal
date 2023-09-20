<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Group;

class GroupModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Group;
        $testcase = array(
            'name'  => 'test',
            'permissions' => "{'admin.view':'1','admin.create':'0'}"
        );
        
        $this->prepare($model, $testcase);
    }
}
