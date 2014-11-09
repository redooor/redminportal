<?php

use Redooor\Redminportal\UserPricelist;

class UserPricelistModelTest extends \RedminTestCase {

    public function testAll()
    {
        $userPricelists = UserPricelist::all();
        $this->assertTrue($userPricelists != null);
    }

    public function testFind1Fails()
    {
        $userPricelist = UserPricelist::find(1);
        $this->assertTrue($userPricelist == null);
    }

    public function testCreateNew()
    {
        $userPricelist = new UserPricelist;
        $userPricelist->user_id = 1;
        $userPricelist->pricelist_id = 1;
        $userPricelist->paid = 10.99;
        $userPricelist->transaction_id = 'UK12345YZ';
        $userPricelist->payment_status = 'Completed';

        $result = $userPricelist->save();

        $this->assertTrue($userPricelist->id == 1);
        $this->assertTrue($userPricelist->user_id == 1);
        $this->assertTrue($userPricelist->pricelist_id == 1);
        $this->assertTrue($userPricelist->paid == 10.99);
        $this->assertTrue($userPricelist->transaction_id == 'UK12345YZ');
        $this->assertTrue($userPricelist->payment_status == 'Completed');
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $userPricelist = UserPricelist::find(1);

        $this->assertTrue($userPricelist != null);
        $this->assertTrue($userPricelist->id == 1);
        $this->assertTrue($userPricelist->user_id == 1);
        $this->assertTrue($userPricelist->pricelist_id == 1);
    }

    public function testPagniate()
    {
        $userPricelists = UserPricelist::paginate(20);
    }

    public function testOrderBy()
    {
        $userPricelists = UserPricelist::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $userPricelists = UserPricelist::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $userPricelist = UserPricelist::find('1');
        $userPricelist->delete();

        $result = UserPricelist::find('1');

        $this->assertTrue($result == null);
    }

}
