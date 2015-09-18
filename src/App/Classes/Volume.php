<?php namespace Redooor\Redminportal\App\Classes;

use Config;
use Redooor\Redminportal\App\Interfaces\Measurable;

class Volume implements Measurable
{
    public static function getUnits()
    {
        $units = Config::get('redminportal::measures.volume');
        
        if (! $units) {
            $units = array('m', 'cm', 'mm', 'in', 'yd');
        }
        
        return $units;
    }
}
