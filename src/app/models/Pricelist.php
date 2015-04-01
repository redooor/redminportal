<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/*  Columns:
 * id (integer)
 * price (float)
 * module_id (integer)
 * membership_id (integer)
 * module_id, membership_id (unique)
 * created_at (date)
 * updated_at (date)
 */

class Pricelist extends Model
{
    protected $table = 'pricelists';
    
    public function module()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Module');
    }

    public function membership()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Membership');
    }
    
    /*
     * Note to contributors: 
     * Discount model will be removed from v0.2.0. 
     * Please use Coupon model instead. 
     */
    public function discounts()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Discount', 'discountable');
    }
    
    public function coupons()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Coupon', 'coupon_pricelist');
    }
    
    public function delete()
    {
        // Remove all relationships
        $this->coupons()->detach();
        
        return parent::delete();
    }
}
