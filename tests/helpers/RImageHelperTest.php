<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Helpers\RImage;

class RImageHelperTest extends RedminTestCase
{
    protected $model;
    protected $library;
    
    /**
     * Contructor.
     * Must be called explicitly by the child
     *
     * @param object $model A new instance of the model to be tested.
     * @param array $testcase An array of property-value to be used in model creation.
     */
    public function __construct()
    {
    }
    
    /**
     * Destructor.
     * Must be called explicitly by the child
     */
    public function __destruct()
    {
        $this->library = null;
        $this->model = null;
    }
    
    /**
     * Test (Pass): check that $model and $testcase are not null for this test
     */
    public function testPropertiesPass()
    {
        $this->library = Config::get('redminportal::image.library', 'gd');
        $this->model = new RImage($this->library);
        
        $this->assertTrue($this->model != null);
    }
}
