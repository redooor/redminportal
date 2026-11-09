<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\UI\Form;

class FormUITest extends RedminTestCase
{
    private $model;
    
    public function setUp(): void
    {
        parent::setUp();
        
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
        
        $output = '<div class="mb-3 biang">
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
        
        $output = '<div class="card">
            <div class="card-header">
                <div class="card-title mb-0">TagsTitle</div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input class="form-control" name="tags" id="tags" value="test1,test2,test3">
                </div>
            </div>
            <div class="card-footer"><i><small>This is a foot note.</small></i></div>
        </div>';
        
        $output = str_replace(array("\r", "\n", " "), '', $output);
        
        $this->assertTrue($input == $output);
    }
    
    public function testEmailInputerPass()
    {
        $input = $this->model->emailInputer(
            'test',
            true
        );
        
        $input = str_replace(array("\r", "\n", " "), '', $input);
        
        $output = '<div class="mb-3 redmin-email-typeahead">
            <label for="email">Email Address</label>
            <input class="form-control typeahead" name="email" id="email" required value="test">
        </div>';
        
        $output = str_replace(array("\r", "\n", " "), '', $output);
        
        $this->assertTrue($input == $output);
    }
    
    /**
     * Test (pass): Search Form
     **/
    public function testSearchFormPass()
    {
        $input = $this->model->searchForm(
            'route/view',
            'route.action',
            ['field_1' => 'Field 1', 'field_2' => 'Field 2'],
            'field_2',
            'test'
        );
        
        $input = str_replace(array("\r", "\n", " "), '', $input);
        
        $output = '<form method="POST" action="route.action" accept-charset="UTF-8" role="form" class="d-flex flex-wrap align-items-center gap-2">
            <input type="hidden" name="_token" value="">
                <div class="input-group w-auto">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                </div>
                <select class="form-select form-select-sm w-auto" name="field">
                    <option value="field_1">Field 1</option>
                    <option value="field_2" selected>Field 2</option>
                </select>
                <div class="input-group w-auto">
                    <input class="form-control form-control-sm" placeholder="Search" 
                        title="Search" name="search" type="text" value="test">
                    <a class="btn btn-secondary btn-sm" href="route/view">
                        <i class="fa-solid fa-xmark"></i> Clear</a>
                </div>
        </form>';
        
        $output = str_replace(array("\r", "\n", " "), '', $output);
        
        $this->assertTrue($input == $output);
    }
    
    public function testSelectorFormPass()
    {
        $input = $this->model->selector(
            'test',
            ['test1', 'test2', 'test3'],
            1,
            [
                'label' => 'testlabel',
                'label_classes' => 'testclass',
                'id' => 'test_id',
                'value_as_key' => true,
                'help_text' => 'This is a help block'
            ]
        );
        
        $input = str_replace(array("\r", "\n", " "), '', $input);
        
        $output = '<div class="mb-3 ">
            <label class="testclass" for="test">testlabel</label>
            <select class="form-select " name="test" id="test_id">
                <option value="test1">test1</option>
                <option value="test2">test2</option>
                <option value="test3">test3</option>
            </select>
            <p class="form-text">This is a help block</p>
        </div>';
        
        $output = str_replace(array("\r", "\n", " "), '', $output);
        
        $this->assertTrue($input == $output);
    }
}
