<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Membership;

class MembershipModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Membership;
        $testcase = array(
            'name' => 'This is the title',
            'rank' => 1
        );
        
        $this->prepare($model, $testcase);
    }
}
