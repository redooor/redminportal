<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\ModuleMediaMembership;

class ModuleMediaMembershipModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new ModuleMediaMembership;
        $testcase = array(
            'module_id' => 1,
            'media_id' => 1,
            'membership_id' => 1
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
