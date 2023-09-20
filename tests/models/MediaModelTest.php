<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Media;

class MediaModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Media;
        $testcase = array(
            'name' => 'This is the title',
            'path' => 'path/to/somewhere',
            'sku' => 'UNIQUESKU001',
            'short_description' => 'This is the body',
            'category_id' => 1,
            'active' => true
        );
        
        $this->prepare($model, $testcase);
    }
}
