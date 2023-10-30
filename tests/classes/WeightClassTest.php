<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Classes\Weight;

class WeightClassTest extends RedminTestCase
{
    private $test_units = array('kg', 'g', 'lbs', 'oz');
    
    /**
     * Test (Pass): test getUnits returns correct array
     */
    public function testGetUnits()
    {
        // Set config for testing
        Config::set('measures.weight', $this->test_units);
        
        $units = Weight::getUnits();
        
        $this->assertTrue($units == $this->test_units);
    }
}
