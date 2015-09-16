<?php namespace Redooor\Redminportal\Test;

use Lang;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Order;

class ProductControllerTest extends BaseControllerTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/products';
        $viewhas = array(
            'singular' => 'product',
            'plural' => 'products'
        );
        $input = array(
            'create' => array(
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'category_id'           => 1,
                'sku'                   => 'UNIQUESKU001',
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            ),
            'edit' => array(
                'id'   => 1,
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'category_id'           => 1,
                'sku'                   => 'UNIQUESKU001',
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            )
        );
        
        parent::__construct($page, $viewhas, $input);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
    }
    
    /**
     * Test (Fail): access postStore with input but no name
     */
    public function testStoreCreateFailsNameBlank()
    {
        $input = array(
            'name'                  => '',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001'
        );

        $this->call('POST', '/admin/products/store', $input);

        $this->assertRedirectedTo('/admin/products/create');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access postStore with product_id and id but no name
     * Edit Product Variant
     */
    public function testStoreEditVariantFailsNameBlank()
    {
        $input = array(
            'name'                  => '',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001',
            'product_id'            => 1,
            'id'                    => 2
        );

        $this->call('POST', '/admin/products/store', $input);

        $this->assertRedirectedTo('/admin/products/edit-variant/1/2');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access postStore with product_id but no id and no name
     * Create Product Variant
     */
    public function testStoreCreateVariantFailsNameBlank()
    {
        $input = array(
            'name'                  => '',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001',
            'product_id'            => 1
        );

        $this->call('POST', '/admin/products/store', $input);

        $this->assertRedirectedTo('/admin/products/create-variant/1');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access postStore with product_id but no id and no name
     * Create Product Variant, no such product with product_id
     */
    public function testStoreCreateVariantFailsNoSuchProduct()
    {
        $input = array(
            'name'                  => 'This is a title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001',
            'product_id'            => 1
        );

        $this->call('POST', '/admin/products/store', $input);

        $this->assertRedirectedTo('/admin/products');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access postStore with product_id and id but no name
     * Edit Product Variant, no such product with product_id
     */
    public function testStoreEditVariantFailsNoSuchProduct()
    {
        $input = array(
            'name'                  => 'This is a title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001',
            'product_id'            => 1,
            'id'                    => 2
        );

        $this->call('POST', '/admin/products/store', $input);

        $this->assertRedirectedTo('/admin/products');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access postStore with product_id but no id
     * Create Product Variant
     */
    public function testStoreCreateVariantPass()
    {
        // Create parent
        $testcase_1 = array(
            'name'                  => 'Product1',
            'sku'                   => 'UNIQUESKU001',
            'short_description'     => 'This is the body',
            'category_id'           => 1,
            'active'                => true
        );
        $parentProduct = $this->createNewModel(new Product, $testcase_1);
        
        $input = array(
            'name'                  => 'This is a variant',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'VARIANT001',
            'product_id'            => $parentProduct->id
        );
        
        $testcase_2 = array(
            'name'                  => 'This is a variant',
            'short_description'     => 'This is body',
            'category_id'           => 1,
            'sku'                   => 'VARIANT001',
            'active'                => false
        );

        $this->call('POST', '/admin/products/store', $input);

        $this->assertRedirectedTo('/admin/products/view-variant/2');
        
        // Check that there's exactly 1 variant
        $this->assertTrue($parentProduct->variants->count() == 1);
        
        foreach ($parentProduct->variants as $variant) {
            // Verify the properties
            $this->assertTrueModelAllTestcases($variant, $testcase_2);
            // Check that there's exactly 1 parent
            $this->assertTrue($variant->variantParents->count() == 1);
        }
    }
    
    /**
     * Test (Fail): access list-variant with invalid product id
     */
    public function testListVariantsFail()
    {
        $this->call('GET', $this->page . '/list-variants/1');
        
        $this->assertRedirectedTo('/admin/products');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access list-variant
     */
    public function testListVariantsPass()
    {
        // Create parent
        $testcase_1 = array(
            'name'                  => 'Product1',
            'sku'                   => 'UNIQUESKU001',
            'short_description'     => 'This is the body',
            'category_id'           => 1,
            'active'                => true
        );
        $parentProduct = $this->createNewModel(new Product, $testcase_1);
        
        // Create variant
        $testcase_2 = array(
            'name'                  => 'Product2',
            'sku'                   => 'VARIANT001',
            'short_description'     => 'This is the body',
            'category_id'           => 1,
            'active'                => true
        );
        $variant = $this->createNewModel(new Product, $testcase_2);
        
        $parentProduct->variants()->attach($variant->id);
        
        $this->call('GET', $this->page . '/list-variants/1');

        $this->assertResponseOk();
        $this->assertViewHas(['variants', 'variantParent', 'imagine']);
    }
    
    /**
     * Test (Fail): access delete-variant-json with invalid product id
     */
    public function testDeleteVariantFailNoSuchProduct()
    {
        $response = $this->call('GET', $this->page . '/delete-variant-json/1');
        
        $array = json_decode($response->getContent());
        $this->assertTrue($array->status == false);
        $this->assertTrue($array->message == Lang::get('redminportal::messages.error_delete_entry'));
    }
    
    /**
     * Test (Fail): access delete-variant-json with product id but product already ordered
     */
    public function testDeleteVariantFailProductInOrder()
    {
        // Create product
        $testcase_1 = array(
            'name'                  => 'Product1',
            'sku'                   => 'UNIQUESKU001',
            'short_description'     => 'This is the body',
            'category_id'           => 1,
            'active'                => true
        );
        $product = $this->createNewModel(new Product, $testcase_1);
        // Create order
        $testcase_2 = array(
            'user_id' => 1,
            'paid' => 10.99,
            'transaction_id' => 'UK12345YZ',
            'payment_status' => 'Completed'
        );
        $order = $this->createNewModel(new Order, $testcase_2);
        $order->products()->attach($product->id);
        
        $response = $this->call('GET', $this->page . '/delete-variant-json/1');
        
        $array = json_decode($response->getContent());
        $this->assertTrue($array->status == false);
        $this->assertTrue($array->message == Lang::get('redminportal::messages.error_delete_product_already_ordered'));
    }
    
    /**
     * Test (Pass): access delete-variant-json with valid product id
     */
    public function testDeleteVariantPass()
    {
        // Create product
        $testcase_1 = array(
            'name'                  => 'Product1',
            'sku'                   => 'UNIQUESKU001',
            'short_description'     => 'This is the body',
            'category_id'           => 1,
            'active'                => true
        );
        $product = $this->createNewModel(new Product, $testcase_1);
        
        $response = $this->call('GET', $this->page . '/delete-variant-json/1');
        
        $array = json_decode($response->getContent());
        $this->assertTrue($array->status == true);
        $this->assertTrue($array->message == Lang::get('redminportal::messages.success_delete_record'));
    }
}
