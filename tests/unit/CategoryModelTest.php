<?php

use Redooor\Redminportal\Category;

class CategoryModelTest extends \RedminTestCase {

    public function testAll()
    {
        $categories = Category::all();
        $this->assertTrue($categories != null);
    }

    public function testFind1Fails()
    {
        $category = Category::find(1);
        $this->assertTrue($category == null);
    }

    public function testCreateNew()
    {
        $category = new Category;
        $category->name = 'This is the title';
        $category->short_description = 'This is the body';
        $category->active = true;

        $result = $category->save();

        $this->assertTrue($category->id == 1);
        $this->assertTrue($category->name == 'This is the title');
        $this->assertTrue($category->short_description == 'This is the body');
        $this->assertTrue($category->active == true);
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $category = Category::find(1);

        $this->assertTrue($category != null);
        $this->assertTrue($category->id == 1);
        $this->assertTrue($category->name == 'This is the title');
    }

    public function testPagniate()
    {
        $categories = Category::paginate(20);
    }

    public function testOrderBy()
    {
        $categories = Category::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $categories = Category::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $category = Category::find('1');
        $category->delete();

        $result = Category::find('1');

        $this->assertTrue($result == null);
    }

}
