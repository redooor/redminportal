<?php

use Redooor\Redminportal\Product;

class ProductModelTest extends \RedminTestCase {

    public function testAll()
    {
        $products = Product::all();
        $this->assertTrue($products != null);
    }

    public function testFind1Fails()
    {
        $product = Product::find(1);
        $this->assertTrue($product == null);
    }

    public function testCreateNew()
    {
        $product = new Product;
        $product->name = 'This is the title';
        $product->sku = 'UNIQUESKU001';
        $product->short_description = 'This is the body';
        $product->category_id = 1;
        $product->active = true;

        $result = $product->save();

        $this->assertTrue($product->id == 1);
        $this->assertTrue($product->name == 'This is the title');
        $this->assertTrue($product->sku == 'UNIQUESKU001');
        $this->assertTrue($product->short_description == 'This is the body');
        $this->assertTrue($product->category_id == 1);
        $this->assertTrue($product->price == 0);
        $this->assertTrue($product->featured == false);
        $this->assertTrue($product->active == true);
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $product = Product::find(1);

        $this->assertTrue($product != null);
        $this->assertTrue($product->id == 1);
        $this->assertTrue($product->name == 'This is the title');
    }

    public function testPagniate()
    {
        $products = Product::paginate(20);
    }

    public function testOrderBy()
    {
        $products = Product::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $products = Product::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $product = Product::find('1');
        $product->delete();

        $result = Product::find('1');

        $this->assertTrue($result == null);
    }

}
