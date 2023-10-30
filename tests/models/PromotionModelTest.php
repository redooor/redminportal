<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Promotion;

class PromotionModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Promotion;
        $testcase = array(
            'name' => 'This is the title',
            'short_description' => 'This is the body',
            'active' => true,
            'start_date' => '2016-02-29 00:00:00',
            'end_date' => '2016-02-29 00:00:00'
        );
        
        $this->prepare($model, $testcase);
    }
}
