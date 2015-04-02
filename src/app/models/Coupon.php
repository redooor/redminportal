<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/*  Columns:
***********************
    id          (increment)
    code        (string)
    description (text, nullable)
    amount      (float)
    is_percent  (boolean, default: false)
    start_date  (dateTime, nullable)
    end_date    (dateTime)
    created_at  (dateTime)
    updated_at  (dateTime)
    max_spent   (float, nullable)
    min_spent   (float, nullable)
    usage_limit_per_coupon  (integer, unsigned, nullable)
    usage_limit_per_user    (integer, unsigned, nullable)
    multiple_coupons        (boolean, default: false)
    exclude_sale_item       (boolean, default: false)
    usage_limit_per_coupon_count    (integer, unsigned, default: 0)
***********************/
class Coupon extends Model
{
    protected $table = 'coupons';
    
    public function pricelists()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Pricelist', 'coupon_pricelist');
    }
    
    public function products()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Product', 'coupon_product');
    }
    
    public function categories()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Category', 'coupon_category');
    }
    
    public function users()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\User', 'coupon_user');
    }
    
    public function delete()
    {
        // Remove all relationships
        $this->pricelists()->detach();
        $this->products()->detach();
        $this->categories()->detach();
        $this->users()->detach();
        
        return parent::delete();
    }
}
