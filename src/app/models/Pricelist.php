<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/*  Columns:
 * id (integer)
 * price (decimal, 8, 2, default 0)
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
