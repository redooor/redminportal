<?php namespace Redooor\Redminportal\App\Models;

use DateTime, Exception;
use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id           (increment)
 * user_id      (integer, unsigned)
 * paid         (decimal, 8, 2, default 0)
 * transaction_id (string, default 'Unknown', nullable)
 * payment_status (string, default 'Completed', nullable)
 * options      (text, nullable)
 * created_at   (dateTime)
 * updated_at   (dateTime)
 *
 */

class Order extends Model
{
    protected $table = 'orders';
    
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];
    
    public function user()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\User');
    }
    
    public function products()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Product', 'order_product');
    }
    
    public function bundles()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Bundle', 'bundle_order');
    }
    
    public function pricelists()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Pricelist', 'order_pricelist');
    }
    
    public function coupons()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Coupon', 'coupon_order');
    }
    
    public function delete()
    {
        // Remove product association
        $this->products()->detach();
        $this->bundles()->detach();
        $this->pricelists()->detach();
        $this->coupons()->detach();
        
        return parent::delete();
    }
    
    /*
     * Return the sum of all products and bundles prices.
     *
     * @return Float
     */
    public function getTotalprice()
    {
        $totalprice = 0;
        $totalprice += $this->bundles()->sum('price');
        $totalprice += $this->products()->sum('price');
        $totalprice += $this->pricelists()->sum('price');
        return $totalprice;
    }
    
    /*
     * Perform a check on the coupon before adding to the order.
     * Recommended if you need to check coupon's multiple_coupons flag before adding it.
     * This is optional, you need to specifically call this method to add a coupon.
     *
     * @return Coupon
     * @throws Exception If coupon cannot be added due to multiple_coupons flag
     */
    public function addCoupon(Coupon $coupon)
    {
        if ($coupon->multiple_coupons) {
            // Allows multiple
            if ($this->coupons()->where('multiple_coupons', false)->count() > 0) {
                // Exists a coupon which doesn't allow multiple
                throw new Exception('Existing coupon does not allow multiple coupons.');
            }
        } else {
            // Doesn't allow multiple
            if ($this->coupons()->count() > 0) {
                // There's an existing coupon in this order
                throw new Exception('This coupon does not allow multiple coupons.');
            }
        }
        
        // All checks passed, proceed to add coupon to order
        $this->coupons()->save($coupon);
        
        return $coupon;
    }
    
    /*
     * Return discounts saved in options['discounts'].
     * If it's empty, call setDiscounts() to verify all coupons and return an array of dicounts 
     * with their corresponding product or bundle.
     * Coupon will be verified on product and bundle level before checking for category.
     *
     * @return array of array('type', 'id', 'name', 'price', 'coupon_id', 'code', 'amount', 'is_percent', 'value')
     */
    public function getDiscounts()
    {
        $options = $this->options;
        
        if ($options) {
            if (array_key_exists('discounts', $options)) {
                return $options['discounts'];
            }
        }
        
        return $this->setDiscounts();
    }
    
    /*
     * Verify and save discounts into options['discounts'].
     * Verify all coupons and return an array of dicounts with their corresponding product or bundle.
     * Coupon will be verified on product and bundle level before checking for category.
     *
     * @return array of array('type', 'id', 'name', 'price', 'coupon_id', 'code', 'amount', 'is_percent', 'value')
     */
    public function setDiscounts()
    {
        $discounts = array();
        
        // Loop through all coupons
        foreach ($this->coupons as $coupon) {
            // Loop through all products
            foreach ($this->products as $product) {
                $product_coupon = $this->verifyCoupon($product, $coupon);
                
                if ($product_coupon) {
                    $discounts[] = $product_coupon;
                    continue;
                }
                
                // Product doesn't qualify for coupon, check if category qualifies
                $category_coupon = $this->verifyCoupon($product->category, $coupon, $product);
                
                if ($category_coupon) {
                    $discounts[] = $category_coupon;
                }
            }
            
            // Loop through all bundles
            foreach ($this->bundles as $bundle) {
                $bundle_coupon = $this->verifyCoupon($bundle, $coupon);
                
                if ($bundle_coupon) {
                    $discounts[] = $bundle_coupon;
                    continue;
                }
                
                // Bundle doesn't qualify for coupon, check if category qualifies
                $category_coupon = $this->verifyCoupon($bundle->category, $coupon, $bundle);
                
                if ($category_coupon) {
                    $discounts[] = $category_coupon;
                }
            }
            
            // Loop through all pricelists
            foreach ($this->pricelists as $pricelist) {
                $pricelist_coupon = $this->verifyCoupon($pricelist, $coupon);
                
                if ($pricelist_coupon) {
                    $discounts[] = $pricelist_coupon;
                    continue;
                }
                
                // Pricelist doesn't qualify for coupon, check if category qualifies
                $category_coupon = $this->verifyCoupon($pricelist->module->category, $coupon, $pricelist);
                
                if ($category_coupon) {
                    $discounts[] = $category_coupon;
                }
            }
        }
        
        $options = $this->options;
        $options['discounts'] = $discounts;
        $this->options = $options;
        $this->save();
        
        return $discounts;
    }
    
    /*
     * Verify if coupon is application to the product or bundle
     *
     * @param $model Object either Product or Bundle
     * @param $coupon Coupon
     * @param $return Object either Product or Bundle to be returned in place of $model
     * @return array ('type', 'id', 'name', 'price', 'coupon_id', 'code', 'amount', 'is_percent', 'value')
     */
    public function verifyCoupon($model, $coupon, $return = null)
    {
        $totalprice = $this->getTotalprice();
        $discount = null;
        $now = new DateTime('now');
        
        // Check if user limit exceeded (only check Completed transaction)
        // Excluding this order
        $coupon_used = $coupon->orders()
            ->where('order_id', '<>', $this->id)
            ->where('user_id', $this->user_id)
            ->where('payment_status', 'Completed')
            ->count();
        if ($coupon->usage_limit_per_user) {
            if ($coupon_used >= $coupon->usage_limit_per_user) {
                return null; // Skip this coupon, already exceeded quota
            }
        }
        
        if (! $model) {
            return null;
        }
        
        // Continue with checks on start_date, end_date, min_spent, max_spent and usage_limit_per_coupon
        $model_coupon = $model->coupons()
            ->where('coupon_id', $coupon->id)
            ->where('coupons.start_date', '<=', $now)
            ->where('coupons.end_date', '>=', $now)
            ->where(function($query) use ($totalprice) {
                $query->orWhere('coupons.min_spent', null)
                    ->orWhere('coupons.min_spent', '<=', $totalprice);
            })
            ->where(function($query) use ($totalprice) {
                $query->orWhere('coupons.max_spent', null)
                    ->orWhere('coupons.max_spent', '>=', $totalprice);
            })
            ->where(function($query) use ($coupon) {
                $query->orWhere('coupons.usage_limit_per_coupon', null)
                    ->orWhere('coupons.usage_limit_per_coupon', '>', $coupon->usage_limit_per_coupon_count);
            })
            ->first();

        if ($model_coupon) {
            if (! $return) {
                $return = $model;
            }
            if ($coupon->is_percent) {
                $value = $return->price * ($coupon->amount / 100);
            } else {
                $value = $coupon->amount;
            }
            $discount = array(
                'type' => get_class($return),
                'id' => $return->id,
                'name' => $return->name,
                'price' => $return->price,
                'coupon_id' => $coupon->id,
                'code' => $coupon->code,
                'amount' => $coupon->amount,
                'is_percent' => $coupon->is_percent,
                'value' => $value
            );
        }
        
        return $discount;
    }
}
