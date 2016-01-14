<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Product;

class CouponControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest;
    
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/coupons';
        $viewhas = array(
            'singular' => 'coupon',
            'plural' => 'models'
        );
        $input = array(
            'create' => array(
                'code' => 'ABC123',
                'description' => 'This is a description',
                'amount' => '10.99',
                'is_percent' => true,
                'start_date' => '02/05/2016 05:39 PM',
                'end_date' => '03/05/2016 05:39 PM',
                'max_spent' => '200.99',
                'min_spent' => '199.88',
                'usage_limit_per_coupon' => 10,
                'usage_limit_per_user' => 1,
                'multiple_coupons' => true,
                'exclude_sale_item' => true,
                'usage_limit_per_coupon_count' => 0,
                'product_id' => array(1),
                'category_id' => array(1),
                'pricelist_id' => array(1)
            ),
            'edit' => array(
                'id'   => 1,
                'code' => 'ABC123',
                'description' => 'This is a description',
                'amount' => '10.99',
                'is_percent' => true,
                'start_date' => '02/05/2016 05:39 PM',
                'end_date' => '03/05/2016 05:39 PM',
                'max_spent' => '200.99',
                'min_spent' => '199.88',
                'usage_limit_per_coupon' => 10,
                'usage_limit_per_user' => 1,
                'multiple_coupons' => true,
                'exclude_sale_item' => true,
                'usage_limit_per_coupon_count' => 0,
                'product_id' => array(1),
                'category_id' => array(1),
                'pricelist_id' => array(1)
            )
        );
        
        // For testing sort
        $this->sortBy = 'start_date';
        
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
     * Setup initial data for use in tests
     */
    public function setup()
    {
        parent::setup();
        
        // Add membership
        $membership = new Membership;
        $membership->name = "Gold";
        $membership->rank = 5;
        $membership->save();
        
        // Add module
        $module = new Module;
        $module->name = 'This is title';
        $module->sku = 'UNIQUESKU001';
        $module->short_description = 'This is body';
        $module->long_description = 'This is long body';
        $module->featured = true;
        $module->active = true;
        $module->category_id = 1;
        $module->save();
                
        // Create a new Pricelist for use later
        $pricelist = new Pricelist;
        $pricelist->module_id = 1;
        $pricelist->membership_id = 1;
        $pricelist->price = 1;
        $pricelist->save();
        
        // Create a new Product for use later
        $product = new Product;
        $product->name = 'This is the title';
        $product->sku = 'UNIQUESKU001';
        $product->short_description = 'This is the body';
        $product->category_id = 1;
        $product->active = true;
        $product->save();
        
        // Create a new Category for use later
        $category = new Category;
        $category->name = 'This is a name';
        $category->short_description = 'This is short description';
        $category->long_description = 'This is long description';
        $category->active = true;
        $category->order = 1;
        $category->save();
    }
    
    /**
     * Test (Pass): store with just product
     */
    public function testStoreWithOnlyProductPass()
    {
        $input = array(
                'code' => 'ABC123',
                'description' => 'This is a description',
                'amount' => '10.99',
                'is_percent' => true,
                'start_date' => '02/05/2016 05:39 PM',
                'end_date' => '03/05/2016 05:39 PM',
                'max_spent' => '200.99',
                'min_spent' => '199.88',
                'usage_limit_per_coupon' => 10,
                'usage_limit_per_user' => 1,
                'multiple_coupons' => true,
                'exclude_sale_item' => true,
                'usage_limit_per_coupon_count' => 0,
                'product_id' => array(1)
            );
        
        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page);
    }
    
    /**
     * Test (Pass): store with just category
     */
    public function testStoreWithOnlyCategoryPass()
    {
        $input = array(
                'code' => 'ABC123',
                'description' => 'This is a description',
                'amount' => '10.99',
                'is_percent' => true,
                'start_date' => '02/05/2016 05:39 PM',
                'end_date' => '03/05/2016 05:39 PM',
                'max_spent' => '200.99',
                'min_spent' => '199.88',
                'usage_limit_per_coupon' => 10,
                'usage_limit_per_user' => 1,
                'multiple_coupons' => true,
                'exclude_sale_item' => true,
                'usage_limit_per_coupon_count' => 0,
                'category_id' => array(1)
            );
        
        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page);
    }
    
    /**
     * Test (Fail): store with just category that doesn't exist
     */
    public function testStoreWithOnlyCategoryFail()
    {
        // Remove all categories to produce an error
        $categories = Category::all();
        foreach ($categories as $cat) {
            $cat->delete();
        }
        
        $input = array(
                'code' => 'ABC123',
                'description' => 'This is a description',
                'amount' => '10.99',
                'is_percent' => true,
                'start_date' => '02/05/2016 05:39 PM',
                'end_date' => '03/05/2016 05:39 PM',
                'max_spent' => '200.99',
                'min_spent' => '199.88',
                'usage_limit_per_coupon' => 10,
                'usage_limit_per_user' => 1,
                'multiple_coupons' => true,
                'exclude_sale_item' => true,
                'usage_limit_per_coupon_count' => 0,
                'category_id' => array(1)
            );
        
        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page . '/create');
        $this->assertSessionHasErrors(); // Fail because the category doesn't exist
    }
    
    /**
     * Test (Pass): store with just pricelist
     */
    public function testStoreWithOnlyPricelistPass()
    {
        $input = array(
                'code' => 'ABC123',
                'description' => 'This is a description',
                'amount' => '10.99',
                'is_percent' => true,
                'start_date' => '02/05/2016 05:39 PM',
                'end_date' => '03/05/2016 05:39 PM',
                'max_spent' => '200.99',
                'min_spent' => '199.88',
                'usage_limit_per_coupon' => 10,
                'usage_limit_per_user' => 1,
                'multiple_coupons' => true,
                'exclude_sale_item' => true,
                'usage_limit_per_coupon_count' => 0,
                'pricelist_id' => array(1)
            );
        
        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page);
    }
    
    /**
     * Test (Fail): store without any product, category or pricelist
     */
    public function testStoreWithoutRestrictedFail()
    {
        $input = array(
                'code' => 'ABC123',
                'description' => 'This is a description',
                'amount' => '10.99',
                'is_percent' => true,
                'start_date' => '02/05/2016 05:39 PM',
                'end_date' => '03/05/2016 05:39 PM',
                'max_spent' => '200.99',
                'min_spent' => '199.88',
                'usage_limit_per_coupon' => 10,
                'usage_limit_per_user' => 1,
                'multiple_coupons' => true,
                'exclude_sale_item' => true,
                'usage_limit_per_coupon_count' => 0
            );
        
        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page . '/create');
        $this->assertSessionHasErrors(); // Fail because there's no restriction specified
    }
    
    /**
     * Test (Fail): store with max spent less than min spent
     */
    public function testStoreWithMaxLessThanMinFail()
    {
        $input = array(
                'code' => 'ABC123',
                'description' => 'This is a description',
                'amount' => '10.99',
                'is_percent' => true,
                'start_date' => '02/05/2016 05:39 PM',
                'end_date' => '03/05/2016 05:39 PM',
                'max_spent' => '200.99',
                'min_spent' => '299.88',
                'usage_limit_per_coupon' => 10,
                'usage_limit_per_user' => 1,
                'multiple_coupons' => true,
                'exclude_sale_item' => true,
                'usage_limit_per_coupon_count' => 0,
                'product_id' => array(1),
                'category_id' => array(1),
                'pricelist_id' => array(1)
            );
        
        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page . '/create');
        $this->assertSessionHasErrors(); // Fail because the category doesn't exist
    }
}
