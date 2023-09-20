<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Portfolio;

class PortfolioModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Portfolio;
        $testcase = array(
            'name' => 'This is the title',
            'short_description' => 'This is the body',
            'category_id' => 1
        );
        
        $this->prepare($model, $testcase);
    }
}
