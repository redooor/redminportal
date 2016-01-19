<?php namespace Redooor\Redminportal\App\Classes;

use Redooor\Redminportal\App\UI\Html;
use Redooor\Redminportal\App\UI\Form;
use Redooor\Redminportal\App\Classes\Imagine;

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
        $this->this_imagine = new Imagine;
    }
    
    public function html()
    {
        return $this->this_html;
    }
    
    public function form()
    {
        return $this->this_form;
    }
    
    public function imagine()
    {
        return $this->this_imagine;
    }
}
