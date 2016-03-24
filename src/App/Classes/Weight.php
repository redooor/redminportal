<?php namespace Redooor\Redminportal\App\Classes;

use Config;
use Redooor\Redminportal\App\Interfaces\Measurable;

class Weight implements Measurable
{
    public static function getUnits()
    {
        $units = Config::get('redminportal::measures.weight');
        
        if (! $units) {
            $units = array('kg', 'g', 'lbs', 'oz');
        }
        
        return $units;
    }
}
