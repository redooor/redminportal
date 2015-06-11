<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Post;

class PostModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Post;
        $testcase = array(
            'title' => 'This is the title',
            'slug' => 'this_is_a_slug',
            'content' => 'This is the body',
            'private' => false,
            'featured' => true,
            'category_id' => 1
        );
        
        parent::__construct($model, $testcase);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
    }
}
