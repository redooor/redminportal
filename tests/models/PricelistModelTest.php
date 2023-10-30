<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Pricelist;

class PricelistModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Pricelist;
        $testcase = array(
            'price' => 0,
            'module_id' => 1,
            'membership_id' => 1,
            'active' => true
        );
        
        $this->prepare($model, $testcase);
    }
}
