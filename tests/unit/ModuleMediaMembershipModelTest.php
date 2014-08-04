<?php

use Redooor\Redminportal\ModuleMediaMembership;

class ModuleMediaMembershipModelTest extends \RedminTestCase {

    public function testAll()
    {
        $moduleMediaMemberships = ModuleMediaMembership::all();
        $this->assertTrue($moduleMediaMemberships != null);
    }

    public function testFind1Fails()
    {
        $moduleMediaMembership = ModuleMediaMembership::find(1);
        $this->assertTrue($moduleMediaMembership == null);
    }

    public function testCreateNew()
    {
        $moduleMediaMembership = new ModuleMediaMembership;
        $moduleMediaMembership->module_id = 1;
        $moduleMediaMembership->media_id = 1;
        $moduleMediaMembership->membership_id = 1;

        $result = $moduleMediaMembership->save();

        $this->assertTrue($moduleMediaMembership->id == 1);
        $this->assertTrue($moduleMediaMembership->module_id == 1);
        $this->assertTrue($moduleMediaMembership->media_id == 1);
        $this->assertTrue($moduleMediaMembership->membership_id == 1);
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $moduleMediaMembership = ModuleMediaMembership::find(1);

        $this->assertTrue($moduleMediaMembership != null);
        $this->assertTrue($moduleMediaMembership->id == 1);
        $this->assertTrue($moduleMediaMembership->module_id == 1);
        $this->assertTrue($moduleMediaMembership->media_id == 1);
        $this->assertTrue($moduleMediaMembership->membership_id == 1);
    }

    public function testPagniate()
    {
        $moduleMediaMemberships = ModuleMediaMembership::paginate(20);
    }

    public function testOrderBy()
    {
        $moduleMediaMemberships = ModuleMediaMembership::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $moduleMediaMemberships = ModuleMediaMembership::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $moduleMediaMembership = ModuleMediaMembership::find('1');
        $moduleMediaMembership->delete();

        $result = ModuleMediaMembership::find('1');

        $this->assertTrue($result == null);
    }

}
