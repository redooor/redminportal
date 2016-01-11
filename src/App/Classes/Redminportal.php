<?php namespace Redooor\Redminportal\App\Classes;

use Redooor\Redminportal\App\UI\Html;

class Redminportal
{
    public $url;
    
    public function __construct($url)
    {
        $this->url = $url;
    }
    
    public function html()
    {
        return new Html;
    }
}
