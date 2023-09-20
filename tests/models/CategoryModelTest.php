<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Category;

class CategoryModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Category;
        $testcase = array(
            'name' => 'This is the title',
            'short_description' => 'This is the body',
            'active' => true
        );
        
        $this->prepare($model, $testcase);
    }
}
