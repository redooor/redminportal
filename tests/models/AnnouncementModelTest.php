<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Announcement;

class AnnouncementModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();
        
        $model = new Announcement;
        $testcase = array(
            'title' => 'This is the title',
            'content' => 'This is the body',
            'private' => false
        );
        
        $this->prepare($model, $testcase);
    }
    
    public function testValidateSuccess()
    {
        $newAnnouncementInput = array(
            'title'     => 'open_., (Public)',
            'content'   => 'This is the body'
        );

        $validation = Announcement::validate($newAnnouncementInput);

        $this->assertTrue($validation->passes());
    }

    public function testValidateFail()
    {
        $newAnnouncementInput = array(
            'title'     => 'open_.,<(Public)>!',
            'content'   => 'This is the body'
        );

        $validation = Announcement::validate($newAnnouncementInput);

        $this->assertTrue(! $validation->passes());
    }
}
