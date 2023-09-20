<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\User;

class UserModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new User;
        $testcase = array(
            'email'     => 'john.doe@example.com',
            'password'  => 'test',
            'activated' => true
        );
        
        $this->prepare($model, $testcase);
    }
}
