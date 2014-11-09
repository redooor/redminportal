<?php

use Redooor\Redminportal\Promotion;

class PromotionModelTest extends \RedminTestCase {

    public function testAll()
    {
        $promotions = Promotion::all();
        $this->assertTrue($promotions != null);
    }

    public function testFind1Fails()
    {
        $promotion = Promotion::find(1);
        $this->assertTrue($promotion == null);
    }

    public function testCreateNew()
    {
        $today = DateTime::createFromFormat('d/m/Y', '29/2/2016');

        $promotion = new Promotion;
        $promotion->name = 'This is the title';
        $promotion->short_description = 'This is the body';
        $promotion->active = true;
        $promotion->start_date = $today;
        $promotion->end_date = $today;

        $result = $promotion->save();

        $this->assertTrue($promotion->id == 1);
        $this->assertTrue($promotion->name == 'This is the title');
        $this->assertTrue($promotion->short_description == 'This is the body');
        $this->assertTrue($promotion->active == true);
        $this->assertTrue($promotion->start_date == $today);
        $this->assertTrue($promotion->end_date == $today);
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $promotion = Promotion::find(1);

        $this->assertTrue($promotion != null);
        $this->assertTrue($promotion->id == 1);
        $this->assertTrue($promotion->name == 'This is the title');
    }

    public function testPagniate()
    {
        $promotions = Promotion::paginate(20);
    }

    public function testOrderBy()
    {
        $promotions = Promotion::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $promotions = Promotion::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $promotion = Promotion::find('1');
        $promotion->delete();

        $result = Promotion::find('1');

        $this->assertTrue($result == null);
    }

}
