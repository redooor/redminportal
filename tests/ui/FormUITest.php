<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\UI\Form;

class FormUITest extends RedminTestCase
{
    private $model;
    
    public function __construct()
    {
        $this->model = new Form;
    }
    
    /**
     * Test (Pass): test inputer() with correct view
     */
    public function testInputerPass()
    {
        $input = $this->model->inputer(
            'tags',
            'test1,test2,test3',
            [
                'id' => 'bleh',
                'class' => 'biang',
                'placeholder' => 'wah',
                'label' => 'label-leh',
                'label_classes' => 'label-class-lor'
            ]
        );
        
        $input = str_replace(array("\r", "\n", " "), '', $input);
        
        $output = '<div class="form-group biang">
            <label class="label-class-lor" for="tags">label-leh</label>
            <input class="form-control" name="tags" id="bleh" placeholder="wah" value="test1,test2,test3">
        </div>';
        
        $output = str_replace(array("\r", "\n", " "), '', $output);
        
        $this->assertTrue($input == $output);
    }
    
    public function testTaggerPass()
    {
        $input = $this->model->tagger(
            'test1,test2,test3',
            'Tags Title',
            'This is a foot note.'
        );
        
        $input = str_replace(array("\r", "\n", " "), '', $input);
        
        $output = '<div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">TagsTitle</div>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <input class="form-control" name="tags" id="tags" value="test1,test2,test3">
                </div>
            </div>
            <div class="panel-footer"><i><small>This is a foot note.</small></i></div>
        </div>';
        
        $output = str_replace(array("\r", "\n", " "), '', $output);
        
        $this->assertTrue($input == $output);
    }
}
