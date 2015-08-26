<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/*  Columns:
 * id (integer)
 * price (decimal, 8, 2, default 0)
 * module_id (integer)
 * membership_id (integer)
 * module_id, membership_id (unique)
 * active (bool)
 * created_at (date)
 * updated_at (date)
 */

class Pricelist extends Model
{
    protected $table = 'pricelists';
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['name'];
    
    /**
     * Get the module name and membership name as Pricelist's name.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        $name = $this->module->name . " (" . $this->membership->name . ")";
        return $name;
    }
    
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
    
    public function bundles()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Bundle', 'bundle_pricelist');
    }
    
    public function orders()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Order', 'order_pricelist');
    }
    
    public function delete()
    {
        // Remove all relationships
        $this->coupons()->detach();
        $this->bundles()->detach();
        $this->orders()->detach();
        
        return parent::delete();
    }
}
