<?php

use Redooor\Redminportal\Purchase;

class PurchaseModelTest extends \RedminTestCase {

    public function testAll()
    {
        $purchases = Purchase::all();
        $this->assertTrue($purchases != null);
    }

    public function testFind1Fails()
    {
        $purchase = Purchase::find(1);
        $this->assertTrue($purchase == null);
    }

    public function testCreateNew()
    {
        $purchase = new Purchase;
        $purchase->user_id = 1;
        $purchase->pricelist_id = 1;

        $result = $purchase->save();

        $this->assertTrue($purchase->id == 1);
        $this->assertTrue($purchase->user_id == 1);
        $this->assertTrue($purchase->pricelist_id == 1);
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $purchase = Purchase::find(1);

        $this->assertTrue($purchase != null);
        $this->assertTrue($purchase->id == 1);
        $this->assertTrue($purchase->user_id == 1);
        $this->assertTrue($purchase->pricelist_id == 1);
    }

    public function testPagniate()
    {
        $purchases = Purchase::paginate(20);
    }

    public function testOrderBy()
    {
        $purchases = Purchase::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $purchases = Purchase::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $purchase = Purchase::find('1');
        $purchase->delete();

        $result = Purchase::find('1');

        $this->assertTrue($result == null);
    }

}
