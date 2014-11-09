<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Membership;

class MembershipModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Membership;
        $testcase = array(
            'name' => 'This is the title',
            'rank' => 1
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
