<?php

use Redooor\Redminportal\Mailinglist;

class MailinglistModelTest extends \RedminTestCase {

    public function testAll()
    {
        $mailinglists = Mailinglist::all();
        $this->assertTrue($mailinglists != null);
    }

    public function testFind1Fails()
    {
        $mailinglist = Mailinglist::find(1);
        $this->assertTrue($mailinglist == null);
    }

    public function testCreateNew()
    {
        $mailinglist = new Mailinglist;
        $mailinglist->email = 'email@test.com';
        $mailinglist->first_name = 'Peter';
        $mailinglist->last_name = 'Lim';
        $mailinglist->active = true;

        $result = $mailinglist->save();

        $this->assertTrue($mailinglist->id == 1);
        $this->assertTrue($mailinglist->email == 'email@test.com');
        $this->assertTrue($mailinglist->first_name == 'Peter');
        $this->assertTrue($mailinglist->last_name == 'Lim');
        $this->assertTrue($mailinglist->active == true);
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $mailinglist = Mailinglist::find(1);

        $this->assertTrue($mailinglist != null);
        $this->assertTrue($mailinglist->id == 1);
        $this->assertTrue($mailinglist->email == 'email@test.com');
        $this->assertTrue($mailinglist->first_name == 'Peter');
        $this->assertTrue($mailinglist->last_name == 'Lim');
        $this->assertTrue($mailinglist->active == true);
    }

    public function testPagniate()
    {
        $mailinglists = Mailinglist::paginate(20);
    }

    public function testOrderBy()
    {
        $mailinglists = Mailinglist::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $mailinglists = Mailinglist::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $mailinglist = Mailinglist::find('1');
        $mailinglist->delete();

        $result = Mailinglist::find('1');

        $this->assertTrue($result == null);
    }

}
