<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Announcement;

class AnnouncementModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Announcement;
        $testcase = array(
            'title' => 'This is the title',
            'content' => 'This is the body',
            'private' => false
        );
        
        parent::__construct($model, $testcase);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
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
