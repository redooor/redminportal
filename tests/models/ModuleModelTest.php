<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Module;

class ModuleModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Module;
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
