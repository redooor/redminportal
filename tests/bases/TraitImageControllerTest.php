<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Image;

trait TraitImageControllerTest
{
    /* Requires
    protected $page
    */
    protected $img_parent_model;
    protected $img_parent_create;

    /**
     * Test (Pass): remove image from model
     */
    public function testRemoveImage()
    {
        $model = $this->createNewModel($this->img_parent_model, $this->img_parent_create);

        // create photo
        $newimage = new Image();
        $newimage->path = "dummy.jpg";

        // save photo to the loaded model
        $model->images()->save($newimage);
        
        $this->call('GET', $this->page . '/imgremove/' . $newimage->id);

        $this->assertResponseStatus(302); // Redirected
        $this->assertRedirectedTo($this->page . '/edit/' . $model->id);
    }
}
