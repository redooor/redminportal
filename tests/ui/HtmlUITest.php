<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\UI\Html;

class HtmlUITest extends RedminTestCase
{
    private $model;
    
    public function __construct()
    {
        $this->model = new Html;
    }
    
    public function testSorterPass()
    {
        $input = $this->model->sorter(
            'test/url',
            'test_name',
            'created_at',
            'asc',
            'Test Name'
        );
        
        $input = str_replace(array("\r", "\n", " "), '', $input);
        
        $output = '<a class="block-header " href="http://localhost/test/url/sort/test_name/asc">Test Name</a>';
        
        $output = str_replace(array("\r", "\n", " "), '', $output);
        
        $this->assertTrue($input == $output);
    }
}
