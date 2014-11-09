<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\User;

class UserModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new User;
        $testcase = array(
            'email'     => 'john.doe@example.com',
            'password'  => 'test',
            'activated' => true
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
