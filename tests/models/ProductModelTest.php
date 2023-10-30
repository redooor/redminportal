<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Product;

class ProductModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Product;
        $testcase = array(
            'name' => 'This is the title',
            'sku' => 'UNIQUESKU001',
            'short_description' => 'This is the body',
            'category_id' => 1,
            'active' => true
        );
        
        $this->prepare($model, $testcase);
    }
    
    /**
     * Test (Pass): create a new record with all properties filled
     */
    public function testCreateNewPass()
    {
        $model = new Product;
        
        $testcase = array(
            'name' => 'This is the title',
            'sku' => 'UNIQUESKU001',
            'short_description' => 'This is the body',
            'long_description' => 'This is a longer body',
            'category_id' => 1,
            'price' => 99999999.99,
            'featured' => true,
            'active' => true,
            'options' => 'A very long text',
            'weight_unit' => 'abc',
            'volume_unit' => 'efg',
            'length' => 99999999.999,
            'width' => 99999999.999,
            'height' => 99999999.999,
            'weight' => 99999999.999
        );
        
        foreach ($testcase as $key => $value) {
            $model->$key = $value;
        }

        $result = $model->save();
        
        $this->assertTrue($result == 1); // Saved successfully
        $this->assertTrue($model->id == 1); // 1st record
        
        $model_check = Product::find(1);
        
        foreach ($testcase as $key => $value) {
            $this->assertTrue($model_check->$key == $value);
        }
    }
}
