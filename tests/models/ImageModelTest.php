<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Image;

class ImageModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Image;
        $testcase = array(
            'path' => '/path/to/image.jpg',
            'imageable_id' => 1,
            'imageable_type' => 'Type'
        );
        
        $this->prepare($model, $testcase);
    }
}
