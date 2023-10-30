<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Helpers\RImage;

class RImageHelperTest extends RedminTestCase
{
    protected $model;
    protected $library;
    
    public function tearDown(): void
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
