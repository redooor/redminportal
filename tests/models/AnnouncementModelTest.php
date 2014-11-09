<?php

use Redooor\Redminportal\Announcement;

class AnnouncementModelTest extends \RedminTestCase {

    public function testAll()
    {
        $announcements = Announcement::all();
        $this->assertTrue($announcements != null);
    }

    public function testFind1Fails()
    {
        $announcement = Announcement::find(1);
        $this->assertTrue($announcement == null);
    }

    public function testCreateNew()
    {
        $announcement = new Announcement;
        $announcement->title = 'This is the title';
        $announcement->content = 'This is the body';
        $announcement->private = FALSE;

        $result = $announcement->save();

        $this->assertTrue($announcement->id == 1);
        $this->assertTrue($announcement->title == 'This is the title');
        $this->assertTrue($announcement->content == 'This is the body');
        $this->assertTrue($announcement->private == FALSE);
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $announcementRepo = new Announcement;
        $announcement = $announcementRepo->find(1);

        $this->assertTrue($announcement != null);
        $this->assertTrue($announcement->id == 1);
        $this->assertTrue($announcement->title == 'This is the title');
    }

    public function testPagniate()
    {
        $announcements = Announcement::paginate(20);
    }

    public function testOrderBy()
    {
        $announcements = Announcement::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $announcements = Announcement::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $announce = Announcement::find('1');
        $announce->delete();

        $result = Announcement::find('1');

        $this->assertTrue($result == null);
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
