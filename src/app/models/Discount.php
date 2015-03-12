<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model {

    /* 
     * Note to contributors: 
     * This model will be removed from v0.2.0. 
     * Please use Coupon model instead. 
     */
    
    /* Members:
    Code
    Expiry_date
    */

    public function pricelist()
    {
        return $this->belongsTo('Redooor\Redminportal\Pricelist');
    }

    public function discountable()
    {
        return $this->morphTo();
    }

}
