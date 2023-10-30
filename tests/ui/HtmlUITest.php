<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\UI\Html;
use Redooor\Redminportal\App\Models\Announcement;
use Redooor\Redminportal\App\Models\Image;

class HtmlUITest extends RedminTestCase
{
    private $model;
    
    public function setUp(): void
    {
        parent::setUp();
        
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
    
    public function testUploadedImagesPass()
    {
        // Set up test model
        $model = new Announcement;
        $model->title = "This is title";
        $model->content = "This is content";
        $model->private = true;
        $model->save();
        
        // create photo
        $newimage = new Image;
        $newimage->path = "dummy.jpg";

        // save photo to the loaded model
        $model->images()->save($newimage);
        
        $input = $this->model->uploadedImages($model);
        
        $input = str_replace(array("\r", "\n", " "), '', $input);
        
        $output = '<div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Uploaded Photos</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <img src="http://localhost/./100x100_crop/dummy.jpg" class="img-thumbnail" alt="">
                        <br><br>
                        <div class="btn-group btn-group-sm">
                            <a href="http://localhost/admin/images/delete/1" class="btn btn-danger btn-confirm">
                                <span class="glyphicon glyphicon-remove"></span>
                            </a>
                            <a href="http://localhost/./800x600/dummy.jpg" class="btn btn-primary btn-copy">
                                <span class="glyphicon glyphicon-link"></span>
                            </a>
                            <a href="http://localhost/./800x600/dummy.jpg" class="btn btn-info" target="_blank">
                                <span class="glyphicon glyphicon-eye-open"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
        
        $output = str_replace(array("\r", "\n", " "), '', $output);
        
        $this->assertTrue($input == $output);
    }
    
    /**
     * Test (pass): Load modalWindow
     **/
    public function testModalWindowPass()
    {
        $input = $this->model->modalWindow(
            'modal_id',
            'This is a title',
            'This is <br> The body',
            '<a href="#">Some button at the footer</a>',
            'modal-lg',
            'modal-progress-id'
        );
        
        $input = str_replace(array("\r", "\n", " "), '', $input);
        
        $output = '<div id="modal_id" class="modal fade">
            <div class="modal-dialog modal-lg">
                <div class="modal-progress">
                    <div class="progress">
                        <div id="modal-progress-id" 
                        class="progress-bar progress-bar-info progress-bar-striped" 
                        role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                            <span class="sr-only">Loading</span>
                        </div>
                    </div>
                </div>
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">This is a title</h4>
                    </div>
                    <div class="modal-body">
                        This is
                        <br> The body
                    </div>
                    <div class="modal-footer">
                        <a href="#">Some button at the footer</a>
                    </div>
                </div>
            </div>
        </div>';
        
        $output = str_replace(array("\r", "\n", " "), '', $output);
        
        $this->assertTrue($input == $output);
    }
}
