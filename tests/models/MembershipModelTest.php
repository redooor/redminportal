<?php

use Redooor\Redminportal\Membership;

class MembershipModelTest extends \RedminTestCase {

    public function testAll()
    {
        $memberships = Membership::all();
        $this->assertTrue($memberships != null);
    }

    public function testFind1Fails()
    {
        $membership = Membership::find(1);
        $this->assertTrue($membership == null);
    }

    public function testCreateNew()
    {
        $membership = new Membership;
        $membership->name = 'This is the title';
        $membership->rank = 1;

        $result = $membership->save();

        $this->assertTrue($membership->id == 1);
        $this->assertTrue($membership->name == 'This is the title');
        $this->assertTrue($membership->rank == 1);
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $membership = Membership::find(1);

        $this->assertTrue($membership != null);
        $this->assertTrue($membership->id == 1);
        $this->assertTrue($membership->name == 'This is the title');
        $this->assertTrue($membership->rank == 1);
    }

    public function testPagniate()
    {
        $memberships = Membership::paginate(20);
    }

    public function testOrderBy()
    {
        $memberships = Membership::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $memberships = Membership::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $membership = Membership::find('1');
        $membership->delete();

        $result = Membership::find('1');

        $this->assertTrue($result == null);
    }

}
