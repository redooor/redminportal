<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Post;

class PostModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Post;
        $testcase = array(
            'title' => 'This is the title',
            'slug' => 'this_is_a_slug',
            'content' => 'This is the body',
            'private' => false,
            'featured' => true,
            'category_id' => 1
        );
        
        $this->prepare($model, $testcase);
    }
}
