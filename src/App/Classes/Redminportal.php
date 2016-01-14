<?php namespace Redooor\Redminportal\App\Classes;

use Redooor\Redminportal\App\UI\Html;
use Redooor\Redminportal\App\UI\Form;

class Redminportal
{
    public $url;
    private $this_html;
    private $this_form;
    
    public function __construct($url)
    {
        $this->url = $url;
        $this->this_html = new Html;
        $this->this_form = new Form;
    }
    
    public function html()
    {
        return $this->this_html;
    }
    
    public function form()
    {
        return $this->this_form;
    }
}
