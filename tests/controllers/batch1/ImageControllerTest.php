<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Redooor\Redminportal\App\Models\Announcement;
use Redooor\Redminportal\App\Models\Image;

class ImageControllerTest extends RedminBrowserTestCase
{
    protected $page;
    
    /**
     * Initialize Setup with seed
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seed('RedminSeeder');
        
        Auth::loginUsingId(1);

        $this->page = '/admin/images';
    }

    public function tearDown(): void
    {
        $this->page = null;
    }
    
    /**
     * Test (Pass): access getIndex
     */
    public function testIndex()
    {
        $this->call('GET', $this->page);

        $this->assertResponseOk();
    }
    
    /**
     * Test (Fail): access getDelete with id = 1
     */
    public function testDeleteFail()
    {
        $this->call('GET', $this->page . '/delete/1');

        $this->assertRedirectedTo('/');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getDelete with id = 1
     */
    public function testDeletePass()
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
        
        $this->call('GET', $this->page . '/delete/' . $newimage->id);

        $this->assertRedirectedTo('/');
    }
}
