<?php namespace Redooor\Redminportal\Test;

use Config;
use Redooor\Redminportal\App\Classes\Volume;

class VolumeClassTest extends RedminTestCase
{
    private $test_units = array('m', 'cm', 'mm', 'in', 'yd');
    
    /**
     * Test (Pass): test getUnits returns correct array
     */
    public function testGetUnits()
    {
        // Set config for testing
        Config::set('measures.volume', $this->test_units);
        
        $units = Volume::getUnits();
        
        $this->assertTrue($units == $this->test_units);
    }
}
