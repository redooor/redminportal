<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Image;

class ImageModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Image;
        $testcase = array(
            'path' => '/path/to/image.jpg',
            'imageable_id' => 1,
            'imageable_type' => 'Type'
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
