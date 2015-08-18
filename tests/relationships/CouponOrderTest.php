<?php namespace Redooor\Redminportal\Test;

use DB, DateTime;
use Redooor\Redminportal\App\Models\Coupon;
use Redooor\Redminportal\App\Models\Order;

class CouponOrderTest extends RedminTestCase
{
    private function createNewOrder()
    {
        $model = new Order;
        $model->user_id = 1;
        $model->paid = 10.99;
        $model->transaction_id = 'UK12345YZ';
        $model->payment_status = 'Completed';
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

    public function testAddCouponsToOrder()
    {
        $order = $this->createNewOrder();
        $check_id = $order->id;

        $coupon_1 = $this->createNewCoupon('UK1234');
        $order->coupons()->save($coupon_1);
        
        $coupon_2 = $this->createNewCoupon('UK8888');
        $order->coupons()->save($coupon_2);
        
        $coupon_3 = $this->createNewCoupon('UK5678');
        $order->coupons()->save($coupon_3);

        $this->assertTrue($order->coupons->count() == 3);
        $this->assertTrue($coupon_1->orders->count() == 1);
        $this->assertTrue($coupon_2->orders->count() == 1);
        $this->assertTrue($coupon_3->orders->count() == 1);
        
        $check_orders = DB::table('coupon_order')->where('order_id', $check_id)->count();
        $this->assertTrue($check_orders == 3);
        
        foreach ($order->coupons as $coupon) {
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
        $order->delete();
        
        $check_orders = DB::table('coupon_order')->where('order_id', $check_id)->count();
        $this->assertTrue($check_orders == 0);
    }
}
