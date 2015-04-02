<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Mailinglist;

class MailinglistModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Mailinglist;
        $testcase = array(
            'email' => 'email@test.com',
            'first_name' => 'Peter',
            'last_name' => 'Lim',
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
