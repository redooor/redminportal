<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Bundle;

class BundleModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Bundle;
        $testcase = array(
            'name' => 'This is the title',
            'sku' => 'UNIQUESKU001',
            'short_description' => 'This is the body',
            'category_id' => 1,
            'active' => true
        );
        
        $this->prepare($model, $testcase);
    }
}
