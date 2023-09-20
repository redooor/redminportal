<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Mailinglist;

class MailinglistModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Mailinglist;
        $testcase = array(
            'email' => 'email@test.com',
            'first_name' => 'Peter',
            'last_name' => 'Lim',
            'active' => true
        );
        
        $this->prepare($model, $testcase);
    }
}
