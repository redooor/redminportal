<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Tag;

class TagModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Tag;
        $testcase = array(
            'name' => 'This is a tag'
        );
        
        $this->prepare($model, $testcase);
    }
}
