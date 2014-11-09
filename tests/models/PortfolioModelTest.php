<?php

use Redooor\Redminportal\Portfolio;

class PortfolioModelTest extends \RedminTestCase {

    public function testAll()
    {
        $portfolios = Portfolio::all();
        $this->assertTrue($portfolios != null);
    }

    public function testFind1Fails()
    {
        $portfolio = Portfolio::find(1);
        $this->assertTrue($portfolio == null);
    }

    public function testCreateNew()
    {
        $portfolio = new Portfolio;
        $portfolio->name = 'This is the title';
        $portfolio->short_description = 'This is the body';
        $portfolio->category_id = 1;

        $result = $portfolio->save();

        $this->assertTrue($portfolio->id == 1);
        $this->assertTrue($portfolio->name == 'This is the title');
        $this->assertTrue($portfolio->short_description == 'This is the body');
        $this->assertTrue($portfolio->category_id == 1);
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $portfolio = Portfolio::find(1);

        $this->assertTrue($portfolio != null);
        $this->assertTrue($portfolio->id == 1);
        $this->assertTrue($portfolio->name == 'This is the title');
    }

    public function testPagniate()
    {
        $portfolios = Portfolio::paginate(20);
    }

    public function testOrderBy()
    {
        $portfolios = Portfolio::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $portfolios = Portfolio::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $portfolio = Portfolio::find('1');
        $portfolio->delete();

        $result = Portfolio::find('1');

        $this->assertTrue($result == null);
    }

}
