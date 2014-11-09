<?php

use Redooor\Redminportal\Pricelist;

class PricelistModelTest extends \RedminTestCase {

    public function testAll()
    {
        $pricelists = Pricelist::all();
        $this->assertTrue($pricelists != null);
    }

    public function testFind1Fails()
    {
        $pricelist = Pricelist::find(1);
        $this->assertTrue($pricelist == null);
    }

    public function testCreateNew()
    {
        $pricelist = new Pricelist;
        $pricelist->price = 0;
        $pricelist->module_id = 1;
        $pricelist->membership_id = 1;

        $result = $pricelist->save();

        $this->assertTrue($pricelist->id == 1);
        $this->assertTrue($pricelist->module_id == 1);
        $this->assertTrue($pricelist->membership_id == 1);
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $pricelist = Pricelist::find(1);

        $this->assertTrue($pricelist != null);
        $this->assertTrue($pricelist->id == 1);
        $this->assertTrue($pricelist->module_id == 1);
        $this->assertTrue($pricelist->membership_id == 1);
    }

    public function testPagniate()
    {
        $pricelists = Pricelist::paginate(20);
    }

    public function testOrderBy()
    {
        $pricelists = Pricelist::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $pricelists = Pricelist::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $pricelist = Pricelist::find('1');
        $pricelist->delete();

        $result = Pricelist::find('1');

        $this->assertTrue($result == null);
    }

}
