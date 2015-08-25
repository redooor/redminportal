<?php namespace Redooor\Redminportal\Test;

use DB, DateTime;
use Redooor\Redminportal\App\Models\Coupon;
use Redooor\Redminportal\App\Models\Bundle;

class BundleCouponTest extends RedminTestCase
{
    private function createNewBundle()
    {
        $model = new Bundle;
        $model->name = 'This is the title';
        $model->sku = 'UNIQUESKU001';
        $model->short_description = 'This is the body';
        $model->category_id = 1;
        $model->active = true;
        $model->save();
        
        return $model;
    }
    
    private function createNewCoupon($code)
    {
        $model = new Coupon;
        $model->code = $code;
        $model->description = "Say something " . $code;
        $model->amount = 10;
        $model->is_percent = true;
        $model->start_date = new DateTime('now');
        $model->end_date = new DateTime('now');
        $model->max_spent = 200;
        $model->min_spent = 1;
        $model->usage_limit_per_coupon = 100;
        $model->usage_limit_per_user = 1;
        $model->multiple_coupons = false;
        $model->exclude_sale_item = false;
        $model->usage_limit_per_coupon_count = 0;
        $model->save();
        
        return $model;
    }

    public function testAddCouponsToBundle()
    {
        $bundle = $this->createNewBundle();
        $check_id = $bundle->id;

        $coupon_1 = $this->createNewCoupon('UK1234');
        $bundle->coupons()->save($coupon_1);
        
        $coupon_2 = $this->createNewCoupon('UK8888');
        $bundle->coupons()->save($coupon_2);
        
        $coupon_3 = $this->createNewCoupon('UK5678');
        $bundle->coupons()->save($coupon_3);

        $this->assertTrue($bundle->coupons->count() == 3);
        $this->assertTrue($coupon_1->bundles->count() == 1);
        $this->assertTrue($coupon_2->bundles->count() == 1);
        $this->assertTrue($coupon_3->bundles->count() == 1);
        
        $check_bundles = DB::table('bundle_coupon')->where('bundle_id', $check_id)->count();
        $this->assertTrue($check_bundles == 3);
        
        foreach ($bundle->coupons as $coupon) {
            if ($coupon->code == 'UK1234') {
                $this->assertTrue($coupon->description == 'Say something UK1234');
            } elseif ($coupon->code == 'UK8888') {
                $this->assertTrue($coupon->description == 'Say something UK8888');
            } elseif ($coupon->code == 'UK5678') {
                $this->assertTrue($coupon->description == 'Say something UK5678');
            } else {
                $this->assertTrue(false);
            }
        }
        
        // Delete order will delete coupons relationship
        $bundle->delete();
        
        $check_bundles = DB::table('bundle_coupon')->where('bundle_id', $check_id)->count();
        $this->assertTrue($check_bundles == 0);
    }
}
