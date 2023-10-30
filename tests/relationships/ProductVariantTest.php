<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\DB;
use Redooor\Redminportal\App\Models\Product;

class ProductVariantTest extends BaseRelationshipTest
{
    public function testAddProductsToParent()
    {
        // Create parent
        $testcase_1 = array(
            'name' => 'Product1',
            'sku' => 'UNIQUESKU001',
            'short_description' => 'This is the body',
            'category_id' => 1,
            'active' => true
        );
        
        $product_1 = $this->createNewModel(new Product, $testcase_1);
        $check_id = $product_1->id;
        
        // Testcase for child
        $testcase_2 = array(
            'name' => 'Childproduct1',
            'sku' => 'UNIQUESKU002',
            'short_description' => 'This is the body',
            'category_id' => 1,
            'active' => true
        );
        
        // Save child into parent
        $product_2 = $this->createNewModel(new Product, $testcase_2);
        $product_1->variants()->save($product_2);

        $this->assertTrue($product_1->variants->count() == 1);
        $this->assertTrue($product_2->variantParents->count() == 1);
        
        $check_parents = DB::table('product_variant')->where('product_id', $check_id)->count();
        $this->assertTrue($check_parents == 1);
        
        foreach ($product_1->variants as $variant) {
            $this->assertTrueModelAllTestcases($variant, $testcase_2);
            foreach ($variant->variantParents as $item) {
                $this->assertTrueModelAllTestcases($item, $testcase_1);
            }
        }
        
        // Delete parent will delete variants relationship
        $product_1->delete();
        
        $check_parents = DB::table('product_variant')->where('product_id', $check_id)->count();
        $this->assertTrue($check_parents == 0);
    }
    
    public function testDeleteProductsFromParent()
    {
        // Create parent
        $testcase_1 = array(
            'name' => 'Product1',
            'sku' => 'UNIQUESKU001',
            'short_description' => 'This is the body',
            'category_id' => 1,
            'active' => true
        );
        
        $product_1 = $this->createNewModel(new Product, $testcase_1);
        $check_id = $product_1->id;
        
        // Testcase for child
        $testcase_2 = array(
            'name' => 'Childproduct1',
            'sku' => 'UNIQUESKU002',
            'short_description' => 'This is the body',
            'category_id' => 1,
            'active' => true
        );
        
        // Save child into parent
        $product_2 = $this->createNewModel(new Product, $testcase_2);
        $product_1->variants()->save($product_2);

        $check_parents = DB::table('product_variant')->where('product_id', $check_id)->count();
        $this->assertTrue($check_parents == 1);
        
        // Delete child will delete variants relationship
        foreach ($product_1->variants as $variant) {
            $variant->delete();
        }
        
        $check_parents = DB::table('product_variant')->where('product_id', $check_id)->count();
        $this->assertTrue($check_parents == 0);
    }
}
