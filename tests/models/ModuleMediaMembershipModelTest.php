<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\ModuleMediaMembership;

class ModuleMediaMembershipModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new ModuleMediaMembership;
        $testcase = array(
            'module_id' => 1,
            'media_id' => 1,
            'membership_id' => 1
        );
        
        $this->prepare($model, $testcase);
    }
}
