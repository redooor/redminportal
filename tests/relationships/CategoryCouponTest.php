<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\DB;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Coupon;

class CategoryCouponTest extends BaseRelationshipTest
{
    protected $model;
    
    /**
     * Setup initial data for use in tests
     */
    public function setup(): void
    {
        parent::setup();
        
        $this->model = $this->createNewModel(new Category, array(
            'name' => 'This is main category',
            'short_description' => 'This is the body',
            'long_description' => 'This is long description',
            'order' => 1,
            'active' => true
        ));
    }
    
    public function testCreateCoupon()
    {
        $check_id = $this->model->id;
        
        $testcase = array(
            'code' => 'ABC123',
            'description' => 'This is a description',
            'amount' => 10.99,
            'is_percent' => true,
            'start_date' => '02/05/2016 5:39 PM',
            'end_date' => '02/05/2016 5:39 PM',
            'max_spent' => 200.99,
            'min_spent' => 199.88,
            'usage_limit_per_coupon' => 10,
            'usage_limit_per_user' => 1,
            'multiple_coupons' => true,
            'exclude_sale_item' => true,
            'usage_limit_per_coupon_count' => 0
        );
        
        $new_coupon = $this->createNewModel(new Coupon, $testcase);
        
        $this->model->coupons()->save($new_coupon);

        $this->assertTrue($this->model->coupons()->count() == 1);
        
        $check_count = DB::table('coupon_category')->where('category_id', $check_id)->count();
        $this->assertTrue($check_count == 1);
        
        foreach ($this->model->coupons as $coupon) {
            $this->assertTrueModelAllTestcases($coupon, $testcase);
        }
        
        // Delete main category will delete all sub categories
        $this->model->delete();

        $check_count = DB::table('coupon_category')->where('category_id', $check_id)->count();
        $this->assertTrue($check_count == 0);
    }
}
