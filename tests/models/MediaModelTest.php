<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Media;

class MediaModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Media;
        $testcase = array(
            'name' => 'This is the title',
            'path' => 'path/to/somewhere',
            'sku' => 'UNIQUESKU001',
            'short_description' => 'This is the body',
            'category_id' => 1,
            'active' => true
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
